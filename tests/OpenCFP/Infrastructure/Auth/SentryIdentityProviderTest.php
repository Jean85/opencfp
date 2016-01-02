<?php

namespace OpenCFP\Infrastructure\Auth;

use Cartalyst\Sentry\Sentry;
use Cartalyst\Sentry\Users;
use Faker\Factory;
use Faker\Generator;
use Mockery as m;
use OpenCFP\Domain\Entity;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Services\NotAuthenticatedException;
use OpenCFP\Domain\Speaker\SpeakerRepository;

class SentryIdentityProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsIdentityProvider()
    {
        $sentry = $this->getSentryMock();
        $speakerRepository = $this->getSpeakerRepositoryMock();

        $provider = new SentryIdentityProvider(
            $sentry,
            $speakerRepository
        );

        $this->assertInstanceOf(IdentityProvider::class, $provider);
    }

    public function testGetCurrentUserThrowsNotAuthenticatedExceptionWhenNotAuthenticated()
    {
        $this->setExpectedException(NotAuthenticatedException::class);

        $sentry = $this->getSentryMock();

        $sentry
            ->shouldReceive('getUser')
            ->once()
            ->andReturnNull()
        ;

        $speakerRepository = $this->getSpeakerRepositoryMock();

        $speakerRepository->shouldNotReceive(m::any());

        $provider = new SentryIdentityProvider(
            $sentry,
            $speakerRepository
        );

        $provider->getCurrentUser();
    }

    public function testGetCurrentUserReturnsUserWhenAuthenticated()
    {
        $id = $this->getFaker()->randomNumber();

        $sentryUser =  $this->getSentryUserMock();

        $sentryUser
            ->shouldReceive('getId')
            ->once()
            ->andReturn($id)
        ;

        $sentry = $this->getSentryMock();

        $sentry
            ->shouldReceive('getUser')
            ->once()
            ->andReturn($sentryUser)
        ;

        $user = $this->getUserMock();

        $speakerRepository = $this->getSpeakerRepositoryMock();

        $speakerRepository
            ->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn($user)
        ;

        $provider = new SentryIdentityProvider(
            $sentry,
            $speakerRepository
        );

        $this->assertSame($user, $provider->getCurrentUser());
    }

    /**
     * @return Generator
     */
    private function getFaker()
    {
        static $faker;

        if ($faker === null) {
            $faker = Factory::create();
        }

        return $faker;
    }

    /**
     * @return m\MockInterface|Sentry
     */
    private function getSentryMock()
    {
        return m::mock(Sentry::class);
    }

    /**
     * @return m\MockInterface|Users\UserInterface
     */
    private function getSentryUserMock()
    {
        return m::mock(Users\UserInterface::class);
    }

    /**
     * @return m\MockInterface|SpeakerRepository
     */
    private function getSpeakerRepositoryMock()
    {
        return m::mock(SpeakerRepository::class);
    }

    /**
     * @return m\MockInterface|Entity\User
     */
    private function getUserMock()
    {
        return m::mock(Entity\User::class);
    }
}