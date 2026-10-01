<?php declare(strict_types=1);

namespace ObMailchimpSync\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Schlanker Client für die Mailchimp Marketing API v3.
 */
class MailchimpClient
{
    private readonly HttpClientInterface $httpClient;

    public function __construct(?HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? HttpClient::create(['timeout' => 8]);
    }

    /**
     * Legt ein Mitglied an oder aktualisiert es (PUT = upsert).
     *
     * @param array<string, string> $mergeFields
     */
    public function upsertMember(string $apiKey, string $audienceId, string $email, string $status, array $mergeFields = []): void
    {
        $body = [
            'email_address' => $email,
            'status_if_new' => $status,
            'status' => $status,
        ];

        if ($mergeFields !== []) {
            $body['merge_fields'] = $mergeFields;
        }

        $this->request(
            $apiKey,
            'PUT',
            \sprintf('/lists/%s/members/%s', rawurlencode($audienceId), self::subscriberHash($email)),
            $body
        );
    }

    /**
     * Batch-Abgleich: bis zu 500 Mitglieder je Aufruf, vorhandene werden aktualisiert.
     *
     * @param list<array{email_address: string, status: string, merge_fields?: array<string, string>}> $members
     *
     * @return array{created: int, updated: int, errors: list<string>}
     */
    public function batchUpsert(string $apiKey, string $audienceId, array $members): array
    {
        $result = ['created' => 0, 'updated' => 0, 'errors' => []];

        foreach (array_chunk($members, 500) as $chunk) {
            $response = $this->request($apiKey, 'POST', \sprintf('/lists/%s', rawurlencode($audienceId)), [
                'members' => $chunk,
                'update_existing' => true,
            ]);

            $result['created'] += \count($response['new_members'] ?? []);
            $result['updated'] += \count($response['updated_members'] ?? []);

            foreach ($response['errors'] ?? [] as $error) {
                $result['errors'][] = ($error['email_address'] ?? '?') . ': ' . ($error['error'] ?? 'Unbekannter Fehler');
            }
        }

        return $result;
    }

    public static function subscriberHash(string $email): string
    {
        return md5(mb_strtolower(trim($email)));
    }

    /**
     * Rechenzentrum steckt im API-Key hinter dem Bindestrich, z. B. "abc123-us21" -> "us21".
     */
    public static function dataCenter(string $apiKey): string
    {
        $parts = explode('-', trim($apiKey));
        $dc = end($parts);

        if (\count($parts) < 2 || !preg_match('/^[a-z]+\d+$/', (string) $dc)) {
            throw new MailchimpException('Ungültiger Mailchimp API-Key (Rechenzentrum fehlt, erwartet z. B. "…-us21").');
        }

        return (string) $dc;
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    private function request(string $apiKey, string $method, string $path, array $body): array
    {
        $url = \sprintf('https://%s.api.mailchimp.com/3.0%s', self::dataCenter($apiKey), $path);

        try {
            $response = $this->httpClient->request($method, $url, [
                'auth_basic' => ['shopware', trim($apiKey)],
                'json' => $body,
            ]);

            $status = $response->getStatusCode();
            $content = $response->getContent(false);
        } catch (\Throwable $e) {
            throw new MailchimpException('Mailchimp nicht erreichbar: ' . $e->getMessage(), 0, $e);
        }

        $data = json_decode($content, true);
        $data = \is_array($data) ? $data : [];

        if ($status >= 400) {
            throw new MailchimpException(\sprintf(
                'Mailchimp-Fehler %d: %s %s',
                $status,
                (string) ($data['title'] ?? ''),
                (string) ($data['detail'] ?? '')
            ));
        }

        return $data;
    }
}
