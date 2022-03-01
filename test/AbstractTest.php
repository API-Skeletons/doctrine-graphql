<?php

namespace ApiSkeletonsTest\Doctrine\GraphQL;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../vendor/autoload.php';

abstract class AbstractTest extends TestCase
{
    protected EntityManager $entityManager;

    public function setUp(): void
    {
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $isDevMode = true;
        $config = Setup::createXMLMetadataConfiguration(array(__DIR__ . "/config"), $isDevMode);

        // database configuration parameters
        $conn = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        // obtaining the entity manager
        $this->entityManager = EntityManager::create($conn, $config);
        $tool = new SchemaTool($this->entityManager);
        $res = $tool->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());

        $this->populateData();
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    protected function populateData()
    {
        $users = [
            [
                'name' => 'User one',
                'email' => 'userOne@gmail.com',
                'password' => 'asdf',
            ],
            [
                'name' => 'User two',
                'email' => 'userTwo@gmail.com',
                'password' => 'fdsa'
            ],
        ];

        $artists = [
            'Grateful Dead' => [
                '1995-02-21' => [
                    'venue' => 'Delta Center',
                    'city' => 'Salt Lake City',
                    'state' => 'Utah',
                    'recordings' => [
                        'SBD> D> CD-R> EAC> SHN; via Jay Serafin, Brian '
                          . 'Walker; see info file and pub comments for notes; '
                          . 'possibly "click track" audible on a couple tracks',
                        'DSBD > 1C > DAT; Seeded to etree by Dan Stephens',
                    ]
                ],
                '1969-11-08' => [
                    'venue' => 'Fillmore Auditorium',
                    'city' => 'San Francisco',
                    'state' => 'California',
                ],
                '1977-05-08' => [
                  'venue' => 'Barton Hall, Cornell University',
                  'city' => 'Ithaca',
                  'state' => 'New York',
                ],
                '1995-07-09' => [
                    'venue' => 'Soldier Field',
                    'city' => 'Chicago',
                    'state' => 'Illinois',
                ],
                '1995-08-09' => [
                    'venue' => null,
                    'city' => null,
                    'state' => null,
                ],
            ],
            'Phish' => [
                '1998-11-02' => [
                    'venue' => 'E Center',
                    'city' => 'West Valley City',
                    'state' => 'Utah',
                    'recordings' => [
                        'AKG480 > Aerco preamp > SBM-1',
                    ],
                ],
                '1999-12-31' => [
                    'venue' => null,
                    'city' => 'Big Cypress',
                    'state' => 'Florida',
                ],
            ],
            'String Cheese Incident' => [
                '2002-06-21' => [
                    'venue' => 'Bonnaroo',
                    'city' => 'Manchester',
                    'state' => 'Tennessee',
                ],
            ],
        ];

        foreach ($users as $userData) {
            $user = new Entity\User();
            $this->entityManager->persist($user);
            $user->setName($userData['name']);
            $user->setEmail($userData['email']);
            $user->setPassword($userData['password']);
        }

        foreach ($artists as $name => $performances) {
            $artist = (new Entity\Artist())
                ->setName($name);
            $this->entityManager->persist($artist);

            foreach ($performances as $performanceDate => $location) {
                $performance = (new Entity\Performance())
                    ->setPerformanceDate(DateTime::createFromFormat('Y-m-d H:i:s', $performanceDate . ' 00:00:00'))
                    ->setVenue($location['venue'])
                    ->setCity($location['city'])
                    ->setState($location['state'])
                    ->setArtist($artist);
                $this->entityManager->persist($performance);

                if (isset($location['recordings'])) {
                    foreach ($location['recordings'] as $source) {
                        $recording = (new Entity\Recording())
                            ->setSource($source)
                            ->setPerformance($performance);
                        $this->entityManager->persist($recording);
                    }

                }
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
