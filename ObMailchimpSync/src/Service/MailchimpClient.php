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
     * Legt einen neuen Kontakt als "subscribed" an. Bestehende Kontakte bleiben UNVERÄNDERT
     * (kein Überschreiben von Status oder Namen – wer sich in Mailchimp abgemeldet hat, bleibt abgemeldet).
     *
     * @param array<string, string> $mergeFields
     *
     * @return bool true = neu angelegt, false = existierte bereits
     */
    public function addMember(string $apiKey, string $audienceId, string $email, array $mergeFields = []): bool
    {
        $body = [
            'email_address' => $email,
            'status' => 'subscribed',
        ];

        if ($mergeFields !== []) {
            $body['merge_fields'] = $mergeFields;
        }

        [$status, $data] = $this->request($apiKey, 'POST', \sprintf('/lists/%s/members', rawurlencode($audienceId)), $body, [400]);

        if ($status === 400) {
            if (($data['title'] ?? '') === 'Member Exists') {
                return false;
            }

            throw self::error($status, $data);
        }

        return true;
    }

    /**
     * Setzt einen bestehenden Kontakt auf "unsubscribed". Unbekannte Adressen werden NICHT angelegt.
     *
     * @return bool true = abgemeldet, false = Kontakt gibt es in Mailchimp nicht
     */
    public function unsubscribeMember(string $apiKey, string $audienceId, string $email): bool
    {
        [$status, $data] = $this->request(
            $apiKey,
            'PATCH',
            \sprintf('/lists/%s/members/%s', rawurlencode($audienceId), self::subscriberHash($email)),
            ['status' => 'unsubscribed'],
            [404]
        );

        return $status !== 404;
    }

    /**
     * Batch: legt bis zu 500 neue Kontakte je Aufruf an. Bestehende Kontakte werden NICHT verändert
     * (update_existing = false).
     *
     * @param list<array{email_address: string, status: string, merge_fields?: array<string, string>}> $members
     *
     * @return array{created: int, existing: int, errors: list<string>}
     */
    public function batchAdd(string $apiKey, string $audienceId, array $members): array
    {
        $result = ['created' => 0, 'existing' => 0, 'errors' => []];

        foreach (array_chunk($members, 500) as $chunk) {
            [, $response] = $this->request($apiKey, 'POST', \sprintf('/lists/%s', rawurlencode($audienceId)), [
                'members' => $chunk,
                'update_existing' => false,
            ]);

            $result['created'] += \count($response['new_members'] ?? []);

            foreach ($response['errors'] ?? [] as $error) {
                if (($error['error_code'] ?? '') === 'ERROR_CONTACT_EXISTS') {
                    ++$result['existing'];
                    continue;
                }

                $result['errors'][] = ($error['email_address'] ?? '?') . ': ' . ($error['error'] ?? 'Unbekannter Fehler');
            }
        }

        return $result;
    }

    /**
     * Prüft API-Key und Zielgruppe.
     *
     * @return array{name: string, memberCount: int}
     */
    public function getAudience(string $apiKey, string $audienceId): array
    {
        [$status, $data] = $this->request(
            $apiKey,
            'GET',
            \sprintf('/lists/%s?fields=name,stats.member_count', rawurlencode($audienceId)),
            [],
            [401, 403, 404]
        );

        if ($status === 401 || $status === 403) {
            throw new MailchimpException('API-Key ungültig oder ohne Berechtigung (Mailchimp: ' . (string) ($data['detail'] ?? $data['title'] ?? $status) . ')');
        }

        if ($status === 404) {
            throw new MailchimpException('Zielgruppen-ID „' . $audienceId . '“ nicht gefunden. Bitte die ID aus Zielgruppe → Einstellungen → Zielgruppenname und Standardwerte kopieren (nicht den Namen).');
        }

        return [
            'name' => (string) ($data['name'] ?? ''),
            'memberCount' => (int) ($data['stats']['member_count'] ?? 0),
        ];
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
     * @param list<int> $allowedErrors HTTP-Fehlercodes, die nicht als Exception geworfen werden
     *
     * @return array{0: int, 1: array<string, mixed>}
     */
    private function request(string $apiKey, string $method, string $path, array $body, array $allowedErrors = []): array
    {
        $url = \sprintf('https://%s.api.mailchimp.com/3.0%s', self::dataCenter($apiKey), $path);

        try {
            $options = ['auth_basic' => ['shopware', trim($apiKey)]];
            if ($body !== []) {
                $options['json'] = $body;
            }

            $response = $this->httpClient->request($method, $url, $options);

            $status = $response->getStatusCode();
            $content = $response->getContent(false);
        } catch (\Throwable $e) {
            throw new MailchimpException('Mailchimp nicht erreichbar: ' . $e->getMessage(), 0, $e);
        }

        $data = json_decode($content, true);
        $data = \is_array($data) ? $data : [];

        if ($status >= 400 && !\in_array($status, $allowedErrors, true)) {
            throw self::error($status, $data);
        }

        return [$status, $data];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function error(int $status, array $data): MailchimpException
    {
        return new MailchimpException(\sprintf(
            'Mailchimp-Fehler %d: %s %s',
            $status,
            (string) ($data['title'] ?? ''),
            (string) ($data['detail'] ?? '')
        ));
    }
}
