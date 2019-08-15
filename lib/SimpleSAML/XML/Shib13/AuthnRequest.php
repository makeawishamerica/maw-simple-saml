<?php

/**
 * The Shibboleth 1.3 Authentication Request. Not part of SAML 1.1,
 * but an extension using query paramters no XML.
 *
 * @author Andreas Åkre Solberg, UNINETT AS. <andreas.solberg@uninett.no>
 * @package SimpleSAMLphp
 * @deprecated This class will be removed in a future release
 */

namespace SimpleSAML\XML\Shib13;

use SimpleSAML\Metadata\MetaDataStorageHandler;

class AuthnRequest
{
    /** @var string|null */
    private $issuer = null;

    /** @var string|null */
    private $relayState = null;


    /**
     * @param string|null $relayState
     * @return void
     */
    public function setRelayState(?string $relayState) : void
    {
        $this->relayState = $relayState;
    }


    /**
     * @return string|null
     */
    public function getRelayState() : ?string
    {
        return $this->relayState;
    }


    /**
     * @param string|null $issuer
     * @return void
     */
    public function setIssuer(?string $issuer) : void
    {
        $this->issuer = $issuer;
    }


    /**
     * @return string|null
     */
    public function getIssuer() : ?string
    {
        return $this->issuer;
    }


    /**
     * @param string $destination
     * @param string $shire
     * @return string
     */
    public function createRedirect(string $destination, string $shire) : string
    {
        $metadata = MetaDataStorageHandler::getMetadataHandler();
        $idpmetadata = $metadata->getMetaDataConfig($destination, 'shib13-idp-remote');

        $desturl = $idpmetadata->getDefaultEndpoint(
            'SingleSignOnService',
            ['urn:mace:shibboleth:1.0:profiles:AuthnRequest']
        );
        $desturl = $desturl['Location'];

        $target = $this->getRelayState();
        $issuer = $this->getIssuer();
        assert($issuer !== null);

        $url = $desturl.'?'.
            'providerId='.urlencode($issuer).
            '&shire='.urlencode($shire).
            (isset($target) ? '&target='.urlencode($target) : '');
        return $url;
    }
}
