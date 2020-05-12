<?php

namespace App\Tests;

use App\Domain\Analytic\ValueObject\AnalyticData;
use App\Domain\Core\ValueObject\Uuid4;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;

class BaseWebTestCase extends WebTestCase
{
    /** @var EntityManager */
    private $entityManager;

    /** @var KernelBrowser */
    private $client;


    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel(['force' => true]);

        $this->entityManager = $this->getEntityManager();
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->entityManager) {
            $this->entityManager->clear(); // avoid memory leaks
            $this->entityManager = null;
        }
        $this->client = null;
    }


    /**
     * @param array $options
     *
     * @return KernelInterface
     */
    protected static function bootKernel(array $options = [])
    {
        return (array_key_exists('force', $options) && $options['force']) || !static::$kernel
            ? parent::bootKernel($options)
            : static::$kernel;
    }


    /**
     * @return EntityManager
     */
    protected function getEntityManager(): EntityManager
    {
        if (!$this->entityManager) {
            $this->entityManager = self::$container->get('doctrine')->getManager();
        }

        return $this->entityManager;
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
    }


    protected static function purgeDatabase(): void
    {
        $manager = self::bootKernel()->getContainer()->get('doctrine')->getManager();

        $purger = new ORMPurger($manager);
        // $purger->setPurgeMode($purger::PURGE_MODE_TRUNCATE);
        $purger->purge();
        self::purgeJsonFiles();
    }

    public static function purgeJsonFiles()
    {
        $di = new RecursiveDirectoryIterator(
            self::$container->getParameter('path_to_customer_storage'), FilesystemIterator::SKIP_DOTS
        );
        $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($ri as $file) {
            $file->isDir() ? rmdir($file) : unlink($file);
        }

        return true;
    }

    /**
     * @param $object
     */
    protected function save($object)
    {
        try {
            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            $this->fail('Can\'t save entity ' . get_class($object) . ': ' . $e->getMessage());
        }
    }


    /**
     * @param bool $recreateKernel
     *
     * @return Client
     */
    protected function getWebClient($recreateKernel = false): Client
    {
        if (!$this->client) {
            $this->client = static::createClient(
                [
                    'environment' => 'test',
                    'debug' => true,
                    'force' => $recreateKernel,
                ]
            );
        }

        return $this->client;
    }

    public function getAnalyticData(): AnalyticData
    {
        return AnalyticData::fromScalar((string)Uuid4::generate(), 'test', 1, (new \DateTime())->format('d-m-Y'));
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $jsonBody
     *
     * @return array
     */
    protected function request(string $method, string $url, array $jsonBody = []): array
    {
        $content = $jsonBody ? json_encode($jsonBody) : null;
        $this->getWebClient()->request($method, $url, [], [], [], $content);
        $response = $this->getWebClient()->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);

        return json_decode($response->getContent(), true);
    }
}
