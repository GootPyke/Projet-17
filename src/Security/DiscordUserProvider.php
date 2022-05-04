<?php 

namespace App\Security;

use App\Repository\UtilisateurRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DiscordUserProvider implements UserProviderInterface
{
    private const DISCORD_ENDPOINT = 'https://discord.com/api/oauth2/token';

    private EventDispatcherInterface $eventDispatcher;

    private HttpClientInterface $httpClient;

    private PasswordGenerator $passwordGenerator;

    private string $discordClientID;

    private string $discordClientSecret;

    private UrlGeneratorInterface $urlGenerator;

    private UtilisateurRepository $userRepository;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HttpClientInterface $httpClient,
        PasswordGenerator $passwordGenerator,
        string $discordClientID,
        string $discordClientSecret,
        UrlGeneratorInterface $urlGenerator,
        UtilisateurRepository $userRepository
    )
    {
        $this->discordClientID = $discordClientID;
        $this->discordClientSecret = $discordClientSecret;
        $this->eventDispatcher = $eventDispatcher;
        $this->passwordGenerator = $passwordGenerator;
        $this->UtilisateurRepository = $UtilisateurRepository;
        $this->httpClient = $httpClient;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the user is not supported
     * @throws UserNotFoundException    if the user is not found
     */
    public function refreshUser(UserInterface $user): UserInterface
    {

    }

    /**
     * Whether this provider supports the given user class.
     *
     * @return bool
     */
    public function supportsClass(string $class): bool
    {

    }

    /**
     * @return UserInterface
     *
     * @throws UserNotFoundException
     *
     * @deprecated since Symfony 5.3, use loadUserByIdentifier() instead
     */
    public function loadUserFromDiscordOAuth(string $code): User
    {
        $accessToken = $this->getAccessToken($code);
    }

    private function getAccessToken(string $code): string
    {
        $redirectURL = $this->urlGenerator->generate('se-co', [
            'discord-oauth-provider' => true
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $options = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ], 
            'body' => [
                'client_id' => $this->discordClientID,
                'client_secret' => $this->discordClientSecret,
                'code' => $code,
                'grant_type' => "authorization_code",
                'redirect_uri' => $redirectURL,
                'scope' => 'identify email'
            ]
        ];

        $response = $this->httpClient->request('POST', self::DISCORD_ENDPOINT, $options);

        $data = $response->toArray();

        if(!$data['access_token']) {
            throw new ServiceUnavailableHttpException("L'authentification via Discord a échoué, veuillez réessayer.");
        }

        return $data['access_token'];
    }
}

?>