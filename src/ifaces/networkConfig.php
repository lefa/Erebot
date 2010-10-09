<?php

ErebotUtils::incl('configProxy.php');

/**
 * \brief
 *      Interface for an IRC network's configuration.
 *
 * This interface provides the necessary methods
 * to represent the configuration associated with
 * some IRC network.
 */
interface   iErebotNetworkConfig
extends     iErebotConfigProxy
{
    /**
     * Creates a new ErebotNetworkConfig instance.
     *
     * \param $mainCfg
     *      A reference to an ErebotMainConfig object which contains the
     *      general configuration for the bot.
     *
     * \param $xml
     *      A SimpleXMLElement node containing the configuration data
     *      for this network.
     */
    public function __construct(iErebotMainConfig &$mainCfg, SimpleXMLElement &$xml);

    /**
     * Returns the name of this IRC network.
     *
     * \return
     *      The name of this IRC network, as a string.
     */
    public function getName();

    /**
     * Returns the configuration object for a particular IRC server.
     *
     * \param $server
     *      The name of the IRC server whose configuration we're interested in.
     *
     * \return
     *      The ErebotServerConfig object for that server.
     *
     * \throw EErebotNotFound
     *      No such server has been configured on this IRC network.
     */
    public function & getServerCfg($server);

    /**
     * Returns all IRC server configurations.
     *
     * \return
     *      A list of ErebotServerConfig instances.
     */
    public function getServers();

    /**
     * Returns the configuration object for a particular IRC channel.
     *
     * \param $channel
     *      The name of the IRC channel whose configuration we're interested in.
     *
     * \return
     *      The ErebotChannelConfig object for that channel.
     *
     * \throw EErebotNotFound
     *      No such channem has been configured on this IRC network.
     */
    public function & getChannelCfg($channel);

    /**
     * Returns all IRC channel configurations.
     *
     * \return
     *      A list of ErebotChannelConfig instances.
     */
    public function getChannels();
}

?>
