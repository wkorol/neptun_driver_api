<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use GuzzleHttp\Promise;


class MamTaxiClient
{
    private readonly Client $httpClient;
    private readonly CookieJarInterface $cookieJar;
    private readonly SessionInterface $session;
    private string $baseUrl = 'https://mamtaxi.pl/';

    public function __construct(private RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();

        if ($this->session->has('mam_taxi_cookie_jar')) {
            $this->cookieJar = unserialize($this->session->get('mam_taxi_cookie_jar'));
        } else {
            $this->cookieJar = new CookieJar(true, []); // persistent cookie jar
        }

        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'cookies' => $this->cookieJar,
            'allow_redirects' => true,
            'headers' => [
                'User-Agent' => 'MamTaxiSymfonyProxy',
                'Accept' => 'application/json, text/html, */*',
            ],
        ]);
    }

    public function login(): bool
    {
        $response = $this->httpClient->get('/Account/Login');
        $html = (string) $response->getBody();

        if (!preg_match('/name="__RequestVerificationToken"\s+type="hidden"\s+value="([^"]+)"/', $html, $matches)) {
            return false;
        }

        $token = $matches[1];

        $response = $this->httpClient->post('/Account/Login', [
            'form_params' => [
                'Login' => "wiktorkorol@gmail.com",
                'Password' => "760661",
                'RememberMe' => 'true',
                '__RequestVerificationToken' => $token,
            ],
            'headers' => [
                'Referer' => $this->baseUrl . '/Account/Login',
                'Origin' => $this->baseUrl,
            ],
        ]);


        foreach ($this->cookieJar->toArray() as $cookie) {
            if ($cookie['Name'] === '.AspNet.ApplicationCookie') {
                $this->requestStack->getSession()->set('mam_taxi_cookie_jar', serialize($this->cookieJar));
                $this->requestStack->getSession()->save();
                return true;
            }
        }

        return false;
    }


    public function logout(): void
    {
        $this->session->remove('mam_taxi_cookie_jar');
    }

    public function isSessionValid(): bool
    {
        try {
            $response = $this->httpClient->get('/api/5550618/Corporation/124/Orders?draw=1&columns[0][data]=Id&columns[0][name]=StartDate&columns[0][searchable]=true&columns[0][search][value]=&columns[0][search][regex]=false&columns[1][data]=CreationDate&columns[1][name]=CreationDate&columns[1][searchable]=true&columns[1][orderable]=false&columns[1][search][value]=&columns[1][search][regex]=false&columns[2][data]=ExternalOrderId&columns[2][name]=ExternalOrderId&columns[2][searchable]=true&columns[2][orderable]=false&columns[2][search][value]=&columns[2][search][regex]=false&columns[3][data]=From&columns[3][name]=From&columns[3][searchable]=true&columns[3][orderable]=false&columns[3][search][value]=&columns[3][search][regex]=false&columns[4][data]=Destination&columns[4][name]=Destination&columns[4][searchable]=true&columns[4][orderable]=false&columns[4][search][value]=&columns[4][search][regex]=false&columns[5][data]=TaxiNumber&columns[5][name]=TaxiNumber&columns[5][searchable]=true&columns[5][orderable]=false&columns[5][search][value]=&columns[5][search][regex]=false&columns[6][data]=Price&columns[6][name]=Price&columns[6][searchable]=true&columns[6][orderable]=false&columns[6][search][value]=&columns[6][search][regex]=false&columns[7][data]=StatusCode&columns[7][name]=StatusCode&columns[7][searchable]=true&columns[7][orderable]=false&columns[7][search][value]=&columns[7][search][regex]=false&columns[8][data]=PaymentMethodCode&columns[8][name]=Payments&columns[8][searchable]=true&columns[8][orderable]=false&columns[8][search][value]=&columns[8][search][regex]=false&columns[9][data]=SecondPaymentMethodCode&columns[9][name]=CashlessPayments&columns[9][searchable]=true&columns[9][orderable]=false&columns[9][search][value]=&columns[9][search][regex]=false&columns[10][data]=Id&columns[10][name]=Id&columns[10][searchable]=true&columns[10][orderable]=false&columns[10][search][value]=&columns[10][search][regex]=false&columns[11][data]=PlannedArrivalDate&columns[11][name]=PlannedArrivalDate&columns[11][searchable]=true&columns[11][orderable]=false&columns[11][search][value]=&columns[11][search][regex]=false&order[0][column]=1&order[0][dir]=desc&start=0&length=1&search[value]=&search[regex]=false&columns[0][orderable]=false&contextTypeId=6', [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Referer' => $this->baseUrl . '/',
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Throwable) {
            return false;
        }
    }

    public function getDebugCookies(): array
    {
        return array_map(fn($cookie) => [
            'name' => $cookie['Name'],
            'value' => $cookie['Value'],
            'domain' => $cookie['Domain'],
            'expires' => $cookie['Expires'],
        ], $this->cookieJar->toArray());
    }

    public function dumpAllOrdersToFiles(
        string $outputDir = __DIR__ . '/../../var/orders',
        string $statePath = __DIR__ . '/../../var/dump_state.json',
        int $max = 10000,
        int $chunk = 10000,
        int $batchSize = 100 // ilość zamówień na jedno zapytanie HTTP
    ): void {
        // Upewnij się, że folder istnieje
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }

        // Wczytaj stan
        $start = 0;
        if (file_exists($statePath)) {
            $state = json_decode(file_get_contents($statePath), true);
            $start = $state['start'] ?? 0;
        }

        $fetched = 0;
        $fileIndex = floor($start / $chunk);

        while ($fetched < $max) {
            $filePath = "$outputDir/orders_$fileIndex.json";
            $file = fopen($filePath, 'w');

            if (!$file) {
                throw new \RuntimeException("Nie udało się otworzyć pliku: $filePath");
            }

            fwrite($file, "[\n");
            $fileFetched = 0;
            $first = true;

            while ($fileFetched < $chunk && $fetched < $max) {
                $url = "/api/5550618/Corporation/124/Orders?draw=1&columns[0][data]=Id&columns[0][name]=StartDate&columns[0][searchable]=true&columns[0][search][value]=&columns[0][search][regex]=false&columns[1][data]=CreationDate&columns[1][name]=CreationDate&columns[1][searchable]=true&columns[1][orderable]=false&columns[1][search][value]=&columns[1][search][regex]=false&columns[2][data]=ExternalOrderId&columns[2][name]=ExternalOrderId&columns[2][searchable]=true&columns[2][orderable]=false&columns[2][search][value]=&columns[2][search][regex]=false&columns[3][data]=From&columns[3][name]=From&columns[3][searchable]=true&columns[3][orderable]=false&columns[3][search][value]=&columns[3][search][regex]=false&columns[4][data]=Destination&columns[4][name]=Destination&columns[4][searchable]=true&columns[4][orderable]=false&columns[4][search][value]=&columns[4][search][regex]=false&columns[5][data]=TaxiNumber&columns[5][name]=TaxiNumber&columns[5][searchable]=true&columns[5][orderable]=false&columns[5][search][value]=&columns[5][search][regex]=false&columns[6][data]=Price&columns[6][name]=Price&columns[6][searchable]=true&columns[6][orderable]=false&columns[6][search][value]=&columns[6][search][regex]=false&columns[7][data]=StatusCode&columns[7][name]=StatusCode&columns[7][searchable]=true&columns[7][orderable]=false&columns[7][search][value]=&columns[7][search][regex]=false&columns[8][data]=PaymentMethodCode&columns[8][name]=Payments&columns[8][searchable]=true&columns[8][orderable]=false&columns[8][search][value]=&columns[8][search][regex]=false&columns[9][data]=SecondPaymentMethodCode&columns[9][name]=CashlessPayments&columns[9][searchable]=true&columns[9][orderable]=false&columns[9][search][value]=&columns[9][search][regex]=false&columns[10][data]=Id&columns[10][name]=Id&columns[10][searchable]=true&columns[10][orderable]=false&columns[10][search][value]=&columns[10][search][regex]=false&columns[11][data]=PlannedArrivalDate&columns[11][name]=PlannedArrivalDate&columns[11][searchable]=true&columns[11][orderable]=false&columns[11][search][value]=&columns[11][search][regex]=false&order[0][column]=1&order[0][dir]=desc&start=$start&length=$batchSize&search[value]=&search[regex]=false&columns[0][orderable]=false&contextTypeId=6"; // tutaj skrócone dla przejrzystości

                $response = $this->httpClient->get($url, [
                    'headers' => [
                        'X-Requested-With' => 'XMLHttpRequest',
                        'Referer' => $this->baseUrl . '/',
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                $orders = $data['data'] ?? [];

                if (count($orders) === 0) {
                    break 2; // brak więcej danych
                }

                foreach ($orders as $order) {
                    $details = [];
                    try {
                        $details = $this->fetchOrderDetails($order['Id']);
                    } catch (\Throwable $e) {}

                    $merged = array_merge($order, $details);
                    if (!$first) {
                        fwrite($file, ",\n");
                    } else {
                        $first = false;
                    }

                    fwrite($file, json_encode($merged, JSON_UNESCAPED_UNICODE));
                    $fetched++;
                    $fileFetched++;
                    $start++;

                    if ($fetched >= $max || $fileFetched >= $chunk) {
                        break;
                    }
                }

                if (count($orders) < $batchSize) {
                    break 2; // nie ma więcej danych
                }

                sleep(1); // delikatny delay
            }

            fwrite($file, "\n]");
            fclose($file);

            // Zapisz stan
            file_put_contents($statePath, json_encode(['start' => $start]));
            $fileIndex++;
        }
    }

    public function fetchOrdersWithDetails(?int $howMany = 200): array
    {

        $response = $this->httpClient->get("/api/5550618/Corporation/124/Orders?draw=1&columns[0][data]=Id&columns[0][name]=StartDate&columns[0][searchable]=true&columns[0][search][value]=&columns[0][search][regex]=false&columns[1][data]=CreationDate&columns[1][name]=CreationDate&columns[1][searchable]=true&columns[1][orderable]=false&columns[1][search][value]=&columns[1][search][regex]=false&columns[2][data]=ExternalOrderId&columns[2][name]=ExternalOrderId&columns[2][searchable]=true&columns[2][orderable]=false&columns[2][search][value]=&columns[2][search][regex]=false&columns[3][data]=From&columns[3][name]=From&columns[3][searchable]=true&columns[3][orderable]=false&columns[3][search][value]=&columns[3][search][regex]=false&columns[4][data]=Destination&columns[4][name]=Destination&columns[4][searchable]=true&columns[4][orderable]=false&columns[4][search][value]=&columns[4][search][regex]=false&columns[5][data]=TaxiNumber&columns[5][name]=TaxiNumber&columns[5][searchable]=true&columns[5][orderable]=false&columns[5][search][value]=&columns[5][search][regex]=false&columns[6][data]=Price&columns[6][name]=Price&columns[6][searchable]=true&columns[6][orderable]=false&columns[6][search][value]=&columns[6][search][regex]=false&columns[7][data]=StatusCode&columns[7][name]=StatusCode&columns[7][searchable]=true&columns[7][orderable]=false&columns[7][search][value]=&columns[7][search][regex]=false&columns[8][data]=PaymentMethodCode&columns[8][name]=Payments&columns[8][searchable]=true&columns[8][orderable]=false&columns[8][search][value]=&columns[8][search][regex]=false&columns[9][data]=SecondPaymentMethodCode&columns[9][name]=CashlessPayments&columns[9][searchable]=true&columns[9][orderable]=false&columns[9][search][value]=&columns[9][search][regex]=false&columns[10][data]=Id&columns[10][name]=Id&columns[10][searchable]=true&columns[10][orderable]=false&columns[10][search][value]=&columns[10][search][regex]=false&columns[11][data]=PlannedArrivalDate&columns[11][name]=PlannedArrivalDate&columns[11][searchable]=true&columns[11][orderable]=false&columns[11][search][value]=&columns[11][search][regex]=false&order[0][column]=1&order[0][dir]=desc&start=0&length=$howMany&search[value]=&search[regex]=false&columns[0][orderable]=false&contextTypeId=6");

        $json = json_decode($response->getBody()->getContents(), true);

        $orders = $json['data'] ?? [];
        $promises = [];
        foreach ($orders as $order) {
            $orderId = $order['Id'];

            $promises[$orderId] = $this->httpClient->getAsync("/api/5550618/Corporation/124/Orders/{$orderId}", [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Referer' => $this->baseUrl . '/',
                ],
            ]);
        }

        $results = Promise\Utils::settle($promises)->wait();


        $merged = [];
        foreach ($orders as $order) {
            $id = $order['Id'];
            if (isset($results[$id]['value'])) {
                $details = json_decode($results[$id]['value']->getBody()->getContents(), true);
                $merged[] = array_merge($order, $details);
            } else {
                $merged[] = $order; // fallback
            }
        }

        return $merged;
    }

    public function fetchOrderDetails(int $id): array
    {
        if (!$this->isSessionValid()) {
            if (!$this->login()) {
                throw new \Exception('Failed');
            }
        }
        $response = $this->httpClient->get("/api/5550618/Corporation/124/Orders/{$id}", [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest',
                'Referer' => $this->baseUrl . '/',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function findDriver(string $id): JsonResponse
    {
        $driverIds = [];
        if (!$this->isSessionValid()) {
            if (!$this->login()) {
                throw new \Exception('Failed');
            }
        }
        for ($i = 15001; $i < 25000; $i++) {
            try {
                $response = $this->httpClient->get("/api/5550618/Driver/0/Drivers/{$i}/Status", [
                    'headers' => [
                        'X-Requested-With' => 'XMLHttpRequest',
                        'Referer' => $this->baseUrl . '/',
                    ],
                ]);
            } catch (\Exception $e) {
                continue;
            }
            $data = json_decode($response->getBody()->getContents(), true);
            if (isset($data['TaxiNo'])) {
                if (in_array($data['TaxiNo'], ['353', '178', '560'])) {
                    $driverIds[] = [$data['TaxiNo'] => $i];
                }
            }
        }
        return new JsonResponse($driverIds);
    }

    public function driverStatuses(): array
    {
        $driverUrls = [
            'https://mamtaxi.pl/api/5550618/Driver/4348/Drivers/4348/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/12266/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/21914/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4406/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25586/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4383/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4414/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4468/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4532/Status',
        ];

        $promises = [];

        foreach ($driverUrls as $url) {
            $promises[] = $this->httpClient->getAsync($url, [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Referer' => $this->baseUrl,
                ],
            ]);
        }

        $responses = \GuzzleHttp\Promise\Utils::settle($promises)->wait();

        $driversStatus = [];

        foreach ($responses as $response) {
            if ($response['state'] === 'fulfilled') {
                $data = json_decode($response['value']->getBody()->getContents(), true);
                $driversStatus[] = [
                    'TaxiNo' => $data['TaxiNo'] ?? null,
                    'Latitude' => $data['Latitude'] ?? null,
                    'Longitude' => $data['Longitude'] ?? null,
                ];
            } else {
                $driversStatus[] = [
                    'TaxiNo' => null,
                    'Latitude' => null,
                    'Longitude' => null,
                    'error' => 'Request failed',
                ];
            }
        }
        return $driversStatus;
    }
}
