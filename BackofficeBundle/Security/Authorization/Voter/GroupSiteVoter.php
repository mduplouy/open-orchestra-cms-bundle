<?php

namespace OpenOrchestra\BackofficeBundle\Security\Authorization\Voter;

use FOS\UserBundle\Model\GroupableInterface;
use OpenOrchestra\BackofficeBundle\Model\GroupInterface;
use OpenOrchestra\BaseBundle\Context\CurrentSiteIdInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class GroupSiteVoter
 */
class GroupSiteVoter implements VoterInterface
{
    protected $contextManager;

    /**
     * @param CurrentSiteIdInterface $contextManager
     */
    public function __construct(CurrentSiteIdInterface $contextManager)
    {
        $this->contextManager = $contextManager;
    }

    /**
     * Checks if the voter supports the given attribute.
     *
     * @param string $attribute An attribute
     *
     * @return bool true if this Voter supports the attribute, false otherwise
     */
    public function supportsAttribute($attribute)
    {
        return 0 === strpos($attribute, 'ROLE_ACCESS');
    }

    /**
     * Checks if the voter supports the given class.
     *
     * @param string $class A class name
     *
     * @return bool true if this Voter can process the class
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * Returns the vote for the given parameters.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @param TokenInterface $token      A TokenInterface instance
     * @param object|null    $object     The object to secure
     * @param array          $attributes An array of attributes associated with the method being invoked
     *
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $result = VoterInterface::ACCESS_ABSTAIN;

        if (($user = $token->getUser()) instanceof GroupableInterface) {
            $roles = $this->extractRoles($user->getGroups());
            $currentSiteId = $this->contextManager->getCurrentSiteId();
            foreach ($attributes as $attribute) {
                if (!$this->supportsAttribute($attribute) ) {
                    continue;
                }
                $result = VoterInterface::ACCESS_DENIED;
                if (array_key_exists($attribute, $roles) && in_array($currentSiteId, $roles[$attribute])) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }

        }

        return $result;
    }

    /**
     * @param array $groups
     *
     * @return array
     */
    protected function extractRoles($groups)
    {
        $roles = array();
        /** @var GroupInterface $group */
        foreach ($groups as $group) {
            $siteId = $group->getSite()->getSiteId();
            foreach ($group->getRoles() as $role) {
                $roles[$role][] = $siteId;
            }
        }

        return $roles;
    }
}
