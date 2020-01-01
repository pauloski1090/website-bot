<?php

namespace App\Tests\Fixtures;

use App\Entity\Agent;
use App\Entity\Faction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AgentFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faction = new Faction();

        $faction->setName('fooTest');

        $manager->persist($faction);

        for ($i = 1; $i <= 5; $i++) {
            $agent = new Agent();

            $agent->setNickname('Agent'.$i);
            $agent->setFaction($faction);

            $manager->persist($agent);
        }

        $manager->flush();
    }
}
