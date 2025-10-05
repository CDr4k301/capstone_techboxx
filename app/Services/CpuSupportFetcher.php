<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class CpuSupportFetcher
{
    protected Client $client;
    protected LoggerInterface $logger;
    protected int $cacheDuration = 86400; // 24 hours

    public function __construct(LoggerInterface $logger)
    {
        $this->client = new Client([
            'timeout' => 10,
            'verify' => true,
        ]);
        $this->logger = $logger;
    }

    /**
     * Fetch CPU support list depending on brand (with caching)
     */
    public function fetch(string $brand, string $modelName): array
    {
        $cacheKey = "cpu_support_{$brand}_{$modelName}";

        return Cache::remember($cacheKey, $this->cacheDuration, function() use ($brand, $modelName) {
            try {
                $brand = strtolower($brand);

                return match($brand) {
                    'asus'      => $this->fetchAsusCpuList($modelName),
                    'msi'       => $this->fetchMsiCpuList($modelName),
                    'gigabyte'  => $this->fetchGigabyteCpuList($modelName),
                    'asrock'    => $this->fetchAsrockCpuList($modelName),
                    default     => []
                };
            } catch (\Exception $e) {
                $this->logger->error("CPU fetch failed for {$brand} {$modelName}: {$e->getMessage()}");
                return [];
            }
        });
    }

    /* ---------------- ASUS ---------------- */
    private function fetchAsusCpuList(string $modelName): array
    {
        try {
            $parts = explode(' ', $modelName);
            $series = strtoupper(array_shift($parts));
            $modelNameNormalized = implode('-', $parts);
            $modelUrlPart = strtolower($modelNameNormalized);

            if ($series === 'ROG') {
                $url = "https://rog.asus.com/motherboards/rog-strix/{$modelUrlPart}/helpdesk_qvl_cpu/";
            } else {
                $url = "https://www.asus.com/motherboards-components/motherboards/{$series}-{$modelUrlPart}/{$modelNameNormalized}/helpdesk_qvl_cpu?model2Name={$modelNameNormalized}";
            }

            $response = $this->client->get($url);
            $html = (string) $response->getBody();
            $crawler = new Crawler($html);
            $cpuNumbers = [];

            $crawler->filter('table tbody tr')->each(function ($row) use (&$cpuNumbers) {
                $cpuNo = $row->filter('td')->eq(1)->text();
                $cpuNumbers[] = trim($cpuNo);
            });

            return $cpuNumbers;
        } catch (\Exception $e) {
            $this->logger->error("ASUS CPU fetch failed for {$modelName}: {$e->getMessage()}");
            return [];
        }
    }

    /* ---------------- MSI ---------------- */
    private function fetchMsiCpuList(string $modelName): array
    {
        try {
            $url = "https://www.msi.com/api/v1/product/support/panel?product=" . urlencode($modelName) . "&type=cpu";
            $data = json_decode($this->client->get($url)->getBody(), true);

            if (!$data || empty($data['data']['cpu'])) return [];

            return array_map(fn($cpu) => $cpu['CPU No'] ?? '', $data['data']['cpu']);
        } catch (\Exception $e) {
            $this->logger->error("MSI CPU fetch failed for {$modelName}: {$e->getMessage()}");
            return [];
        }
    }

    /* ---------------- Gigabyte ---------------- */
    private function fetchGigabyteCpuList(string $modelName): array
    {
        try {
            $searchUrl = "https://www.gigabyte.com/Search?kw=" . urlencode($modelName);
            $html = $this->client->get($searchUrl)->getBody()->getContents();
            $crawler = new Crawler($html);

            $link = $crawler->filter('.search-list a')->first()->attr('href') ?? null;
            if (!$link) return [];

            $productHtml = $this->client->get("https://www.gigabyte.com{$link}")->getBody()->getContents();
            $productCrawler = new Crawler($productHtml);

            $productId = $productCrawler->filter('#isPid')->attr('value') ?? null;
            if (!$productId) return [];

            $url = "https://www.gigabyte.com/Ajax/SupportFunction/Getcpulist?Type=Product&Value={$productId}";
            $data = json_decode($this->client->get($url)->getBody(), true);

            if (!$data || empty($data['CPUList'])) return [];

            return array_map(fn($cpu) => $cpu['CPU Model'] ?? '', $data['CPUList']);
        } catch (\Exception $e) {
            $this->logger->error("Gigabyte CPU fetch failed for {$modelName}: {$e->getMessage()}");
            return [];
        }
    }

    /* ---------------- ASRock ---------------- */
    private function fetchAsrockCpuList(string $modelName): array
    {
        try {
            $url = "https://www.asrock.com/mb/productGet.asp?cat=CPU&ln=&Model=" . urlencode($modelName);
            $data = json_decode($this->client->get($url)->getBody(), true);

            if (!$data || $data['status'] !== 'ok') return [];

            $contents = $data['contents'] ?? [];
            $chunkSize = 11;
            $cpus = [];

            foreach (array_chunk($contents, $chunkSize) as $cpuRow) {
                $series = $cpuRow[1] ?? '';
                $name   = $cpuRow[2] ?? '';
                if ($series || $name) {
                    $cpus[] = trim($series . ' ' . $name);
                }
            }

            return $cpus;
        } catch (\Exception $e) {
            $this->logger->error("ASRock CPU fetch failed for {$modelName}: {$e->getMessage()}");
            return [];
        }
    }
}
