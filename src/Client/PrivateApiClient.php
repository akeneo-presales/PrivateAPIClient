<?php
namespace App\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;

class PrivateApiClient
{
    const REQUEST_TYPE_GET = 'GET';
    const REQUEST_TYPE_POST = 'POST';
    /**
     * @var Client
     */
    private $client;
    private $accessInitilized = false;

    public function __construct(
        private array $configuration
    )
    {
        $this->client = new Client([
            'base_uri' => $this->configuration['pim_url'],
            'cookies' => true
        ]);
    }

    public function getUsers()
    {
        return $this->getGenericGridRecords('pim-user-grid');
    }

    private function getGenericGridRecords($gridName, $additionnalArguments = null)
    {
        $this->initAccess();
        $nbPerPage = 100;
        $page = 1;

        $additionnalArgumentsString = '';

        if(null !== $additionnalArguments && count($additionnalArguments) > 0) {
            foreach($additionnalArguments as $keyArg => $valueArg) {
                $additionnalArgumentsString .= $keyArg.'='.$valueArg.'&' . $gridName . '%5B'.$keyArg.'%5D=' . $valueArg.'&';
            }
            $additionnalArgumentsString = substr($additionnalArgumentsString, 0, -1);
        }

        $result = json_decode($this->makeRequestWithJsonResult('/datagrid/' . $gridName . '/load?' . $gridName . '%5B_pager%5D%5B_page%5D=' . $page . '&' . $gridName . '%5B_pager%5D%5B_per_page%5D=' . $nbPerPage.$additionnalArgumentsString)->data);
        $records = $result->data;

        if(null == $records) {
            $records = [];
        }

        if ($result->options->totalRecords > count($records)) {
            $nbPages = (int)ceil($result->options->totalRecords / $nbPerPage);
            for ($page++; $page <= $nbPages; $page++) {
                $result = json_decode($this->makeRequestWithJsonResult('/datagrid/' . $gridName . '/load?' . $gridName . '%5B_pager%5D%5B_page%5D=' . $page . '&' . $gridName . '%5B_pager%5D%5B_per_page%5D=' . $nbPerPage)->data);
                $records = array_merge($records, $result->data);
            }
        }

        foreach ($records as $key => $record) {
            $records[$key] = $this->makeRequestWithJsonResult($record->delete_link);
        }
        return $records;
    }


    public function initAccess()
    {
        if (false === $this->accessInitilized) {
            try {
                $contents = $this->makeRequest('/');

                if (preg_match('/(<form .* <\/form>)/s', $contents['body'], $matches)) {
                    $dom = new \DOMDocument();
                    $dom->loadHTML($matches[1]);

                    $xpath = new \DOMXPath($dom);

                    $tags = $xpath->query('//input');

                    foreach ($tags as $tag) {
                        $formInputs[trim($tag->getAttribute('name'))] = trim($tag->getAttribute('value'));
                    }

                    $formInputs['_username'] = $this->configuration['admin_username'];
                    $formInputs['_password'] = $this->configuration['admin_password'];

                    $result = $this->makeRequest('/user/login-check', self::REQUEST_TYPE_POST, [
                        'form_params' => $formInputs
                    ]);

                    if (preg_match('/Invalid credentials/', $result['body'])) {
                        throw new Exception('Invalid Credentials');
                    }

                    $this->accessInitilized = true;
                }
            } catch (\Exception $e) {
                $this->accessInitilized = false;
                $message = 'Error when WEB Access initialized : ' . $e->getMessage();
                throw new Exception($message);
            }
        }
    }


    /**
     * @param string $endpoint
     * @param string $type
     * @param array $datas
     * @return array
     */
    public function makeRequest($endpoint, $type = self::REQUEST_TYPE_GET, $datas = [])
    {
        /** @var Response $response */
        try {
            $response = $this->client->request($type, $endpoint, $datas);
            return ['code' => $response->getStatusCode(), 'body' => $response->getBody()->getContents()];
        } catch (RequestException $e) {
            return ['code' => $e->getCode(), 'body' => !is_null($e->getResponse()) ? $e->getResponse()->getBody()->getContents() : $e->getMessage()];
        }

    }

    /**
     * @param $endpoint
     * @param string $type
     * @param array $datas
     * @return mixed
     */
    public function makeRequestWithJsonResult($endpoint, $type = self::REQUEST_TYPE_GET, $datas = [])
    {
        $res = $this->makeRequest($endpoint, $type, $datas);

        return json_decode($res['body']);
    }
}
