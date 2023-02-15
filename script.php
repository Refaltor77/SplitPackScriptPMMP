<?php

// Spécifier la taille maximale de chaque petit pack en octets
$maxPackSize = 2000000; // 2 Mo

// Ouvrir le pack d'origine
$zip = zip_open("sounds.mcpack"); // mettez le pack que vous voulez


if ($zip) {
    // Initialiser les variables pour le nouveau pack
    $packNumber = 1;
    $currentPackSize = 0;
    $newZip = new ZipArchive();
    $newZip->open("devision_" . $packNumber . ".mcpack", ZipArchive::CREATE);
    $fileYml['resource_stack'][] = "devision_" . $packNumber . ".mcpack";


    // Parcourir tous les fichiers du pack d'origine
    while ($zip_entry = zip_read($zip)) {
        // Lire les informations sur le fichier
        $entryName = zip_entry_name($zip_entry);
        $entrySize = zip_entry_filesize($zip_entry);
        $entryData = zip_entry_read($zip_entry, $entrySize);

        // Vérifier si le fichier doit être copié dans le nouveau pack actuel ou le nouveau pack suivant
        if (($currentPackSize + $entrySize) > $maxPackSize) {
            $currentPackSize = 0;
            $newZip->addFromString("manifest.json", updateManifestUUID($newZip->getFromName("manifest.json")));
            $newZip->close();
            $packNumber++;
            $newZip = new ZipArchive();
            $newZip->open("division_" . $packNumber . ".mcpack", ZipArchive::CREATE);
        }

        // Copier le fichier dans le nouveau pack actuel
        $newZip->addFromString($entryName, $entryData);
        $currentPackSize += $entrySize;
    }

    // Mettre à jour le manifeste pour le dernier pack généré
    $newZip->addFromString("manifest.json", updateManifestUUID($newZip->getFromName("manifest.json")));

    // Fermer tous les packs
    zip_close($zip);
    $newZip->close();
}


// Fonction pour mettre à jour les UUID du manifeste
function updateManifestUUID($manifestData) {
    $manifest = json_decode($manifestData, true);
    $manifest['format_version'] = 2;
    $manifest['header']['uuid'] = generateUUID();
    $manifest['header']['description'] = "Pack optimize by script PHP edited by refaltor le bg";
    $manifest['header']['version'] = [0, 0, 1];
    $manifest['header']['min_engine_version'] = [1, 19, 0];
    $manifest['header']['name'] = "GOLDRUSH_PACK_OPTIMIZE_" . uniqid();
    $manifest['modules'][] = [
        'description' => "Pack optimize by script PHP edited by refaltor le bg",
        'type' => 'resources',
        'uuid' => generateUUID(),
        'version' => [0, 0, 1]
    ];

    return json_encode($manifest);
}

// Fonction pour générer un UUID aléatoire
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
