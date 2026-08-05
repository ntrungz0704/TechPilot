<?php

class RateLimiter
{
    private string $filePath;

    public function __construct()
    {
        $this->filePath = sys_get_temp_dir() . '/techpilot_rate_limits.json';
    }

    private function readData(): array
    {
        if (!file_exists($this->filePath)) {
            return [];
        }
        $content = file_get_contents($this->filePath);
        if (!$content) {
            return [];
        }
        $data = json_decode($content, true);
        if (!is_array($data)) {
            return [];
        }
        return $data;
    }

    private function writeData(array $data): void
    {
        file_put_contents($this->filePath, json_encode($data));
    }

    private function cleanup(array &$data): void
    {
        $now = time();
        foreach ($data as $key => $info) {
            if ($now >= $info['expires_at']) {
                unset($data[$key]);
            }
        }
    }

    public function tooManyAttempts(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        $data = $this->readData();
        $this->cleanup($data);
        
        if (isset($data[$key])) {
            if ($data[$key]['attempts'] >= $maxAttempts) {
                // Keep data updated in case of cleanup
                $this->writeData($data);
                return true;
            }
        }
        
        $this->writeData($data);
        return false;
    }

    public function hit(string $key, int $decayMinutes = 15): void
    {
        $data = $this->readData();
        $this->cleanup($data);

        $now = time();
        if (isset($data[$key])) {
            $data[$key]['attempts']++;
        } else {
            $data[$key] = [
                'attempts' => 1,
                'expires_at' => $now + ($decayMinutes * 60)
            ];
        }

        $this->writeData($data);
    }

    public function clear(string $key): void
    {
        $data = $this->readData();
        if (isset($data[$key])) {
            unset($data[$key]);
            $this->writeData($data);
        }
    }
}
