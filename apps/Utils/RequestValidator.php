<?php

namespace App\Utils;

class RequestValidator
{
    public static function validate(array $rules, array $data = null): array
    {
        $data = $data ?? json_decode(file_get_contents("php://input"), true);
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach (explode('|', $fieldRules) as $rule) {
                if ($rule === 'required' && is_null($value)) {
                    $errors[$field][] = 'is required';
                }

                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = 'must be a valid email';
                }

                if (str_starts_with($rule, 'min:')) {
                    $min = (int) explode(':', $rule)[1];
                    if (strlen($value ?? '') < $min) {
                        $errors[$field][] = "must be at least $min characters";
                    }
                }

                if (str_starts_with($rule, 'max:')) {
                    $max = (int) explode(':', $rule)[1];
                    if (strlen($value ?? '') > $max) {
                        $errors[$field][] = "must not exceed $max characters";
                    }
                }

                if ($rule === 'json' && is_string($value)) {
                    json_decode($value);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $errors[$field][] = 'must be a valid JSON string';
                    }
                }
            }
        }

        if (!empty($errors)) {
            Response::error(422, 'Validation failed', $errors);
        }

        return $data;
    }

    // public static function sanitize(array $data): array
    // {
    //     $clean = [];
    //     foreach ($data as $key => $value) {
    //         $clean[$key] = is_string($value)
    //             ? htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8')
    //             : $value;
    //     }
    //     return $clean;
    // }

    public static function sanitize(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            $clean[$key] = is_string($value)
                ? trim($value)
                : $value;
        }
        return $clean;
    }

    /**
     * Validate and parse an ID.
     * Only allows alphanumeric, dash (-), and underscore (_).
     *
     * @param string $id
     * @return string
     */
    public static function parseId(string $id): string
    {
        if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $id)) {
            Response::error(400, "Invalid ID format.");
        }

        return $id;
    }


    /**
     * Validate and parse a URL.
     * Only supports http and https schemes.
     *
     * @param string $url
     * @return string
     */
    public static function parseUrl(string $url): string
    {

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Response::error(400, "Invalid URL format.");
        }

        $parsed = parse_url($url);
        $scheme = strtolower($parsed['scheme'] ?? '');

        $allowedSchemes = ['http', 'https'];
        if (!in_array($scheme, $allowedSchemes, true)) {
            Response::error(400, "Unsupported URL scheme. Only http/https allowed.");
        }

        return $url;
    }
}
