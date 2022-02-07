<?php
/**
 * Meetup GraphQL HTTP Client
 *
 * @author     Rajat Patel
 * @package    HttpClient
 */

/**
 * Meetup GraphQL HTTP Client class.
 *
 * @since      1.0.0
 * @category   Class
 * @package    HttpClient
 */
class Import_Meetup_Events_HttpClient{
    
    /**
    * grapgql_query function.
    *
    * @access protected
    * @return cURL object
    */
    public function graphql_query(string $endpoint, string $query, array $variables = [], ?string $token = null): array
    {
        $headers = ['Content-Type: application/json'];
        if (null !== $token) {
            $headers[] = "Authorization: bearer $token";
        }

        if (false === $data = @file_get_contents($endpoint, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $headers,
                'content' => json_encode(['query' => $query, 'variables' => $variables]),
            ]
        ]))) {
            $error = error_get_last();
            throw new \ErrorException($error['message'], $error['type']);
        }

        return json_decode($data, true);
    }
}