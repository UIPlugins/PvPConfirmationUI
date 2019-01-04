<?php
declare(strict_types=1);

namespace ARTulloss\PvPUI;

use ARTulloss\LibBoolUI\YesNoForm;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

/**
 * Class PvPUI
 * @package Author\PvPUI
 * @author ARTulloss
 */
class PvPUI extends PluginBase implements Listener
{
	public function onEnable(): void
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * @param EntityDamageEvent $event
	 * @priority LOW
	 */
	public function onCombat(EntityDamageEvent $event): void{
		$entity = $event->getEntity();
		if($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager();
			if ($event->getCause() !== 666 && $damager instanceof Player) {
				$this->sendForm($damager, $entity, $event);
				$event->setCancelled();
			}
		}
	}

	/**
	 * @param Player $player
	 * @param Entity $entity
	 * @param EntityDamageEvent $event
	 */
	public function sendForm(Player $player, Entity $entity, EntityDamageEvent $event): void
	{
		$rand = (bool) mt_rand(0,1); // Make this plugin more functional to make sure they're  actually paying attention;

		$closure = function (Player $player, $data) use ($event, $rand, $entity): void
		{
			$event = clone $event;

			if($data)
				$event->setCancelled(false);

			// Set the cause by reflection TY @CortexPE

			$refClass = new \ReflectionClass(EntityDamageEvent::class);
			$refProp = $refClass->getProperty("cause");
			$refProp->setAccessible(true);
			$refProp->setValue($event, 666);
			$entity->attack($event);
		};

		$form = new YesNoForm($closure, 'Are you sure?');

		$form->randomize();

		$form->setForced();

		$form->registerButtons();

		$form->setImage($form::YES, false, 'textures/ui/checkboxFilledYellow');
		$form->setImage($form::NO, false, 'textures/ui/checkboxUnFilled');

		$form->setContent("Are you sure you want to hit this player?");

		$player->sendForm($form);
	}
}