<?php
declare(strict_types=1);

namespace ARTulloss\PvPUI;

use ARTulloss\PvPUI\libs\ARTulloss\libBoolUI;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;


/**
 * Class PvPUI
 * @package ARTulloss\PvPUI
 * @author ARTulloss
 */
class PvPUI extends PluginBase implements Listener
{
    /** @var integer $incrementer */
    private $incrementer;

	public function onEnable(): void
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
	}

	/**
	 * @param EntityDamageEvent $event
	 * @priority LOW
	 */
	public function onCombat(EntityDamageEvent $event): void{
	    $config = $this->getConfig();
	    if($config->get('Incrementer') && $this->incrementer > (int) $config->get('Stop After'))
	        return;
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
        $config = $this->getConfig();
		$closure = function (Player $player, $data) use ($config, $event, $entity): void
		{
			$event = clone $event;

			if($data)
				$event->setCancelled(false);

			// Set the cause by reflection TY @CortexPE

			$refClass = new \ReflectionClass(EntityDamageEvent::class);
			$refProp = $refClass->getProperty('cause');
			$refProp->setAccessible(true);
			$refProp->setValue($event, 666);
			$entity->attack($event);
            if($config->get('Incrementer'))
                $this->incrementer++;
		};

		$form = new YesNoForm($closure, 'Are you sure?');

		if($config->get('Randomized'))
			$form->randomize();

		if($config->get('Forced'))
			$form->setForced();

		$form->registerButtons();

		if($config->get('Images')) {
			$form->setImage(YesNoForm::YES, false, 'textures/ui/checkboxFilledYellow');
			$form->setImage(YesNoForm::NO, false, 'textures/ui/checkboxUnFilled');
		}

		$form->setContent($config->get('Question'));

		$player->sendForm($form);
	}
}
