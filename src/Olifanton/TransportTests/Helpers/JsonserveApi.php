<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Helpers;

use Olifanton\TransportTests\HttpClient;

final class JsonserveApi
{
    /**
     * @throws \JsonException|\Http\Client\Exception
     */
    public static function putJson(array|\JsonSerializable $data): string
    {
        $response = HttpClient::discovery()->post(
            "https://api.jsonserve.com/data",
            [
                "Content-Type" => "application/json",
                "Accept" => "application/json",
            ],
            json_encode([
                "json" => json_encode($data, JSON_UNESCAPED_UNICODE & JSON_THROW_ON_ERROR),
            ], JSON_UNESCAPED_UNICODE & JSON_THROW_ON_ERROR),
        );

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException(sprintf(
                "Jsonserve.com API error: HTTP status %d, Response body: %s",
                $response->getStatusCode(),
                $response->getBody()->getContents(),
            ));
        }

        $apiResponseBody = $response->getBody()->getContents();
        $apiResponse = json_decode($apiResponseBody, true, flags: JSON_THROW_ON_ERROR);

        if (isset($apiResponse["url"])) {
            return $apiResponse["url"];
        }

        throw new \RuntimeException(sprintf("Jsonserve.com API error: Unexpected answer: %s", $apiResponseBody));
    }
}
