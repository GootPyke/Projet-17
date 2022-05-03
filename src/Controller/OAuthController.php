<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OAuthController extends AbstractController
{
    private const DISCORD_ENDPOINT = 'https://discord.com/api/oauth2/authorize';

    /**
     * @Route("/oauth/discord", name="oauth", methods={"GET"})
     */
    public function connexionAvecDiscord(
        CsrfTokenManagerInterface $ctmi,
        UrlGeneratorInterface $urlGenerator
    ): RedirectResponse
    {

        // https://127.0.0.1:8000/connexion?discord-oauth-provider=1
        $redirectURL = $urlGenerator->generate('se-co', [
            'discord-oauth-provider' => true
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        // dd($redirectURL);

        $queryParams = http_build_query([
            'client_id' => $this->getParameter('app.discord_client_id'),
            'prompt' => 'consent',
            'redirect_uri' => $redirectURL,
            'response_type' => 'code',
            'scope' => 'identify email',
            'state' => $ctmi->getToken('oauth-discord-SF')->getValue()
        ]);

        return new RedirectResponse(self::DISCORD_ENDPOINT . '?' . $queryParams);
    }
}
