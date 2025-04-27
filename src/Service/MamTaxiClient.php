<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use GuzzleHttp\Promise;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;


class MamTaxiClient
{
    private readonly Client $httpClient;
    private readonly CookieJarInterface $cookieJar;
    private ?SessionInterface $session = null;
    private string $baseUrl = 'https://mamtaxi.pl/';
    private string $cookieFile = __DIR__ . '/../../var/mam_taxi_cookie_jar.ser';
    private bool $cliContext = false;



    public function __construct(
        private RequestStack $requestStack,
        private CacheInterface $cache,
    ) {
        $request = $this->requestStack->getCurrentRequest();

        if ($request !== null) {
            try {
                if ($request->hasSession()) {
                    $this->session = $request->getSession();
                }
            } catch (SessionNotFoundException) {
                $this->cliContext = true;
            }
        } else {
            $this->cliContext = true;
        }

        if ($this->session && $this->session->has('mam_taxi_cookie_jar')) {
            $this->cookieJar = unserialize($this->session->get('mam_taxi_cookie_jar'));
        } elseif (file_exists($this->cookieFile)) {
            $this->cookieJar = unserialize(file_get_contents($this->cookieFile));
        } else {
            $this->cookieJar = new CookieJar(true, []);
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
                if ($this->session && $this->session->isStarted()) {
                    $this->session->set('mam_taxi_cookie_jar', serialize($this->cookieJar));
                    $this->session->save();
                }

                file_put_contents($this->cookieFile, serialize($this->cookieJar));

                return true;
            }
        }

        return false;
    }

    public function logout(): void
    {
        if ($this->session) {
            $this->session->remove('mam_taxi_cookie_jar');
        }
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
            if ($this->cliContext) {
                return $this->login();
            }
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
        for ($i = 4000; $i < 35000; $i++) {
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
                if (in_array($data['TaxiNo'],  ["376"])) {
                    $driverIds[] = [$data['TaxiNo'] => $i];
                }
            }
        }
        return new JsonResponse($driverIds);
    }

    public function driverStatuses(): array
    {
        return $this->cache->get('mam_taxi_driver_statuses', function (ItemInterface $item) {
            $item->expiresAfter(60);
            return []; // fallback
        });
    }

    public function refreshDriverStatuses(): array
    {
        $data = $this->fetchDriverStatuses();

        $item = $this->cache->getItem('mam_taxi_driver_statuses');
        $item->set($data)->expiresAfter(60);
        $this->cache->save($item);

        return $data;
    }

    public function fetchDriverStatuses(): array
    {
        if ($this->isSessionValid()) {
            $this->login();
        }
        $driverUrls = [
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4348/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/12266/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4351/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/28483/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23798/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4632/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/28426/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4675/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31556/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/26549/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4653/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/21060/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23737/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31089/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4577/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/30609/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4579/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/21394/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4735/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25695/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4450/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4494/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4610/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/30451/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4728/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4472/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25806/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4408/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/30567/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4420/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/26257/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/20283/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24352/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31493/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/28518/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4525/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4350/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4651/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4387/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/21347/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25976/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23140/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/20522/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31417/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/17037/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/28425/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4407/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4596/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4451/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/14343/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29941/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4476/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23226/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4505/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4510/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4596/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4464/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4600/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4362/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4614/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4437/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4426/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4378/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4400/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/17502/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31472/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/14394/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/26101/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24255/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/30419/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25933/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/12203/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/28436/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/18914/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/13427/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/16961/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24261/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4655/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4630/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/17974/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4386/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4528/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4628/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4617/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4586/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4527/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4518/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4402/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4452/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4341/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25932/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31473/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24321/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4370/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25619/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/28443/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23622/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23156/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4750/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4506/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/20234/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25072/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4591/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31407/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/30568/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4661/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4553/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4590/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4583/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4431/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4394/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4374/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29917/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24428/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/30421/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/30423/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29940/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25928/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/26256/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4738/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/28521/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/26547/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23652/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4629/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25476/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31380/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23155/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25694/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4763/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4700/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4703/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4467/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4706/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4662/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4488/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23501/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25821/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4449/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24437/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/17536/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23076/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29621/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4453/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23108/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24153/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29912/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29914/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25929/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25975/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/26099/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4681/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24553/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4540/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4739/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4682/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4647/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4621/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4583/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4601/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4564/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4526/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4511/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4454/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4500/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4433/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4389/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4359/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/21914/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4406/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25586/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4383/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4414/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4468/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4532/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29782/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4748/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/30003/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/30753/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4335/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4391/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4410/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4413/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4415/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4484/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4541/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4546/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4548/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4585/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4626/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4683/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4695/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4746/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23461/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/26551/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4331/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4379/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4384/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4385/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4427/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4434/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4457/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4466/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4486/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4504/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4517/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4522/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4539/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4547/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4556/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4560/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4567/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4575/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4604/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4668/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4689/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4692/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4702/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4711/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/12426/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/16188/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/17116/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/17617/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/20233/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23177/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23352/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23799/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23852/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24278/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24280/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24328/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24431/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24443/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25043/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25044/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25104/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25116/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25587/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25610/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25738/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25743/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25748/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25927/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/25934/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/26550/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/28438/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29293/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29790/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29792/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/30676/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31023/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4340/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4361/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4377/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4397/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4429/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4435/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4483/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4497/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4515/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4521/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4552/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4555/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4562/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4571/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4598/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4607/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4624/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4640/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4641/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4664/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4671/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4701/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4742/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31628/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/22746/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/22821/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23658/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/23897/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24219/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/26105/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/26517/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29646/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29913/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31192/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31268/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/31437/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/15456/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/28446/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/29781/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/24134/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4446/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4334/Status',
            'https://mamtaxi.pl/api/5550618/Driver/0/Drivers/4354/Status',
        ];

        $driversStatus = [];
        $requests = function ($driverUrls) {
            foreach ($driverUrls as $url) {
                yield new Request('GET', $url, [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Referer' => $this->baseUrl,
                ]);
            }
        };

        $pool = new Pool($this->httpClient, $requests($driverUrls), [
            'concurrency' => 20,
            'fulfilled' => function ($response, $index) use (&$driversStatus, $driverUrls) {
                $data = json_decode($response->getBody()->getContents(), true);
                $driversStatus[] = [
                    'TaxiNo' => $data['TaxiNo'] ?? null,
                    'Latitude' => $data['Latitude'] ?? null,
                    'Longitude' => $data['Longitude'] ?? null,
                    'Status' => $data['Status'] ?? null,
                ];
            },
            'rejected' => function (RequestException $reason, $index) use (&$driversStatus, $driverUrls) {
                $driversStatus[] = [
                    'TaxiNo' => null,
                    'Latitude' => null,
                    'Longitude' => null,
                    'Status' => null,
                    'error' => 'Request failed',
                ];
            },
        ]);

        // Start!
        $promise = $pool->promise();
        $promise->wait();

        return $driversStatus;
    }
}
