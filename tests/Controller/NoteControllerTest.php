<?php

namespace App\Tests\Controller;

use App\Entity\Note;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class NoteControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/note/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Note::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Note index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'note[author]' => 'Testing',
            'note[topic]' => 'Testing',
            'note[content]' => 'Testing',
            'note[contentType]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Note();
        $fixture->setAuthor('My Title');
        $fixture->setTopic('My Title');
        $fixture->setContent('My Title');
        $fixture->setContentType('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Note');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Note();
        $fixture->setAuthor('Value');
        $fixture->setTopic('Value');
        $fixture->setContent('Value');
        $fixture->setContentType('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'note[author]' => 'Something New',
            'note[topic]' => 'Something New',
            'note[content]' => 'Something New',
            'note[contentType]' => 'Something New',
        ]);

        self::assertResponseRedirects('/note/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getAuthor());
        self::assertSame('Something New', $fixture[0]->getTopic());
        self::assertSame('Something New', $fixture[0]->getContent());
        self::assertSame('Something New', $fixture[0]->getContentType());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Note();
        $fixture->setAuthor('Value');
        $fixture->setTopic('Value');
        $fixture->setContent('Value');
        $fixture->setContentType('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/note/');
        self::assertSame(0, $this->repository->count([]));
    }
}
