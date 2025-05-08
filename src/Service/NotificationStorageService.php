<?php

// src/Service/NotificationStorageService.php
namespace App\Service;

class NotificationStorageService
{
    private string $storagePath;

    public function __construct(string $storageDir)
    {
        $this->storagePath = $storageDir.'/entretien_notifications.json';
        
        // Crée le répertoire s'il n'existe pas
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0777, true);
        }
    }

    public function getStoragePath(): string
    {
        return $this->storagePath;
    }
}