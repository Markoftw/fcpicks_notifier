<?php

namespace Markoftw\fcpicks;

use GuzzleHttp\Client;
use GuzzleHttp\Ring\Exception\RingException;
use InvalidArgumentException;
use Markoftw\fcpicks\DB\DB;
use Markoftw\fcpicks\Mailer\Mailer;
use Symfony\Component\DomCrawler\Crawler;

class FCpicks
{
    /**
     * Website URL
     * @var string
     */
    protected $url = "http://www.fcpicks.com";

    /**
     * Guzzle client
     * @var Client
     */
    protected $client;

    /**
     * HTML full page response
     * @var Client
     */
    protected $response;

    /**
     * Save fetched results
     * @var array
     */
    protected $results;

    /**
     * @var bool
     */
    protected $errors;

    /**
     * FCpicks constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->errors = false;
        $this->response = null;
    }

    /**
     * Get page content and save it
     * @return $this
     */
    public function getPageContent()
    {

        $response = $this->client->get($this->url);

        if ($response->getStatusCode() == 200) {
            $this->response = $response->getBody()->getContents();
        } else {
            echo "Status code: " . $response->getStatusCode();
        }

        return $this;
    }

    /**
     * Show page output
     * @return mixed
     */
    public function showPage()
    {
        return $this->response;
    }

    /**
     * Find new picks
     * @return $this
     */
    public function findNewPosts()
    {
        if ($this->response != null) {
            $parser = new Crawler($this->response);

            $crawler = $parser->filterXPath('//tr[@class="active"]');

            if($crawler->count() > 0) {

                try {
                    $this->results = $crawler->each(
                        function (Crawler $node, $i) {
                            $first = $node->children()->first()->text();
                            $second = $node->children()->eq(1)->text();
                            $third = str_replace(' Preview', '', $node->children()->eq(2)->text());
                            $fourth = $node->children()->eq(3)->text();
                            $fifth = $node->children()->eq(4)->text();
                            $last = $node->children()->last()->text();
                            $arr = array($first, $second, $third, $fourth, $fifth, $last);
                            return array_map('trim', $arr);
                        }
                    );
                } catch (InvalidArgumentException $e) {
                    $this->errors = true;
                    echo 'Invalid Argument Exception: ' . $e->getMessage();
                } catch (RingException $e) {
                    $this->errors = true;
                    echo 'Ring Exception: ' . $e->getMessage();
                } 
            } else {
                $this->errors = true;
                echo "No new picks.";
            }
        } else {
            $this->errors = true;
            echo "Response is null";
        }

        return $this;
    }

    /**
     * Show results
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }


    /**
     * Send emails if new results exist
     */
    public function mailResults()
    {
        if (!$this->errors && $this->response != null) {
            foreach ($this->results as $result) {
                if (!$this->checkIfExists($result)) {
                    DB::getInstance()->insert("history", array(
                        'datum' => $result[0],
                        'countryname' => $result[1],
                        'matchvs' => $result[2],
                        'infavor' => $result[3],
                        'betodds' => $result[4],
                        'betwebsite' => $result[5],
                    ));
                    Mailer::sendMsg($result);
                }
            }
        }
    }

    /**
     * Check if match is already in database
     * @param array $arr
     * @return bool
     */
    private function checkIfExists(array $arr)
    {
        $exists = DB::getInstance()->query('SELECT * FROM history WHERE datum = ? AND countryname = ? AND matchvs = ? AND infavor = ? AND betodds = ? AND betwebsite = ?', array(
            $arr[0], $arr[1], $arr[2], $arr[3], $arr[4], $arr[5]
        ));
        if ($exists->count()) {
            return true;
        }
        return false;
    }


}