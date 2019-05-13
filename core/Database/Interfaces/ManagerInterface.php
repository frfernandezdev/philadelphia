<?php 

  namespace Core\Database\Interfaces;

	use Core\Interfaces\SettingsInterface;

  interface ManagerInterface {
    /**
     * Set settings to PDO for connected to Database.
     */
    public function __construct(SettingsInterface $settings);
  }