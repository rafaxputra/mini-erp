<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TrimInputFilter implements FilterInterface
{
    private const SKIP_KEYS = [
        'password',
        'password_confirm',
        'current_password',
        'new_password',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        if (! empty($_POST)) {
            $_POST = $this->normalizeArray($_POST);
            $request->setGlobal('post', $_POST);
        }

        if (! empty($_GET)) {
            $_GET = $this->normalizeArray($_GET);
            $request->setGlobal('get', $_GET);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function normalizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && in_array($key, self::SKIP_KEYS, true)) {
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->normalizeArray($value);
            } elseif (is_string($value)) {
                $trimmed = trim($value);
                if ($key === 'code') {
                    $trimmed = preg_replace('/\s+/', '', $trimmed) ?? $trimmed;
                }
                $data[$key] = $trimmed;
            }
        }

        return $data;
    }
}
