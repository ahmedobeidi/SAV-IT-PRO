<?php

namespace App\DataFixtures;

use App\Entity\EquipmentBrand;
use App\Entity\EquipmentModel;
use App\Entity\EquipmentType;
use App\Entity\Issue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EquipmentFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $types = [
            'Téléphone', 'Ordinateur portable', 'Tablette', 'PC fixe', 'Console de jeux',
            'Montre connectée', 'Imprimante', 'Moniteur', 'Routeur', 'NAS',
            'Appareil photo', 'Projecteur', 'Terminal de paiement', 'Lecteur de codes-barres', 'Téléviseur',
            'Home cinéma', 'Drone', 'Serveur', 'Mini PC', 'Liseuse',
            'Casque VR', 'Scanner', 'Table de mixage audio', 'Microphone', 'Enceinte',
            'Casque audio', 'Hub domotique', 'Sonnette connectée avec caméra', 'Centrale d’alarme', 'Unité de contrôle d’accès',
            'Caisse enregistreuse', 'Imprimante d’étiquettes', 'Borne interactive', 'Client léger', 'Station d’accueil',
            'SSD externe', 'Commutateur réseau', 'Pare-feu matériel', 'Point d’accès Wi-Fi', 'Onduleur',
            'MacBook', 'iMac', 'Station de travail', 'Tablette médicale', 'Chromebook éducatif',
            'Terminal industriel', 'Panneau tactile', 'Terminal de pointage', 'Interphone', 'Téléphone VoIP'
        ];

        $brands = [
            'Apple', 'Samsung', 'Dell', 'HP', 'Lenovo',
            'Asus', 'Acer', 'MSI', 'Microsoft', 'Huawei',
            'Xiaomi', 'Google', 'Sony', 'LG', 'Canon',
            'Epson', 'Brother', 'Zebra', 'Honeywell', 'Cisco',
            'Ubiquiti', 'Netgear', 'TP-Link', 'Synology', 'QNAP',
            'Bose', 'JBL', 'Logitech', 'Razer', 'Alienware',
            'BenQ', 'ViewSonic', 'Panasonic', 'Philips', 'Toshiba',
            'Fujitsu', 'Sharp', 'Ricoh', 'Kyocera', 'Nikon',
            'GoPro', 'DJI', 'Creality', 'Elgato', 'Poly',
            'Yealink', 'Mitel', 'Avaya', 'Intel NUC', 'AOC'
        ];

        $models = [
            'iPhone 13', 'Galaxy S23', 'XPS 15', 'Spectre x360', 'ThinkPad X1 Carbon',
            'ZenBook 14', 'Aspire 5', 'Surface Laptop 5', 'MateBook X Pro', 'Pixel 8',
            'Redmi Note 13', 'PlayStation 5', 'Xbox Series X', 'Nintendo Switch OLED', 'Apple Watch Series 9',
            'Galaxy Watch 6', 'iPad Air', 'Tab S9', 'Inspiron 14', 'Victus 16',
            'ROG Strix G16', 'MacBook Air M3', 'MacBook Pro 14', 'iMac 24', 'Pro Display XDR',
            'LaserJet Pro M404', 'EcoTank ET-2850', 'HL-L3270CDW', 'ZQ620', 'Voyager 1202g',
            'UniFi Dream Machine', 'RT-AX88U', 'TL-SG3428', 'DS923+', 'TS-464',
            'WH-1000XM5', 'QuietComfort Ultra', 'Bar 800', 'MX Master 3S', 'DeathAdder V3',
            'OptiPlex 7010', 'EliteDesk 800', 'ThinkCentre M90q', 'Surface Pro 10', 'Chromebook Plus 515',
            'Intercom Pro', 'VoIP Desk 86', 'Attendance One', 'Secure Panel X', 'Touch Kiosk 22'
        ];

        $issues = [
            'Écran cassé', 'Panne de batterie', 'Port de charge endommagé', 'Ne s’allume plus',
            'Dégât des eaux', 'Problème de clavier', 'Pavé tactile non fonctionnel', 'Surchauffe',
            'Bruit de ventilateur', 'Problème de connexion Wi-Fi', 'Bluetooth défaillant', 'Son déformé',
            'Microphone défectueux', 'Caméra non fonctionnelle', 'Ne démarre pas', 'Écran bleu',
            'Lenteur du système', 'Panne de stockage', 'Erreur mémoire RAM', 'Carte mère défectueuse',
            'Bouton power cassé', 'Écran tactile non réactif', 'Bourrage papier', 'Mauvaise qualité d’impression',
            'Scanner non détecté', 'Problème d’alimentation papier', 'Erreur de cartouche', 'Écran qui scintille',
            'Pixels morts', 'Port HDMI défectueux', 'Port USB défectueux', 'Port Ethernet défaillant',
            'Firmware corrompu', 'Réinstallation du système nécessaire', 'Problème BIOS', 'Problème de connexion',
            'Prise jack défectueuse', 'Charnière cassée', 'Problème de rétroéclairage', 'Face ID défaillant',
            'Lecteur d’empreinte défectueux', 'GPS non fonctionnel', 'Carte SIM non détectée', 'Coupures réseau',
            'Redémarrage en boucle du routeur', 'Erreur disque NAS', 'Surcharge électrique', 'Écran tactile fissuré',
            'Bouton bloqué', 'Panne intermittente inconnue'
        ];

        $typeEntities = [];
        $brandEntities = [];
        $modelEntities = [];
        $issueEntities = [];

        foreach ($types as $i => $name) {
            $type = (new EquipmentType())
                ->setName($name)
                ->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($type);
            $typeEntities[] = $type;
            $this->addReference('equipment_type.dynamic_' . ($i + 1), $type);
        }

        $this->addReference('equipment_type.phone', $typeEntities[0]);
        $this->addReference('equipment_type.laptop', $typeEntities[1]);

        foreach ($brands as $i => $name) {
            $type = $typeEntities[$i % count($typeEntities)];

            $brand = (new EquipmentBrand())
                ->setName($name)
                ->setEquipmentType($type)
                ->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($brand);
            $brandEntities[] = $brand;
            $this->addReference('equipment_brand.dynamic_' . ($i + 1), $brand);
        }

        $this->addReference('equipment_brand.apple', $brandEntities[0]);
        $this->addReference('equipment_brand.dell', $brandEntities[2]);

        foreach ($models as $i => $name) {
            $brand = $brandEntities[$i % count($brandEntities)];

            $model = (new EquipmentModel())
                ->setName($name)
                ->setEquipmentBrand($brand)
                ->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($model);
            $modelEntities[] = $model;
            $this->addReference('equipment_model.dynamic_' . ($i + 1), $model);
        }

        $this->addReference('equipment_model.iphone13', $modelEntities[0]);
        $this->addReference('equipment_model.xps15', $modelEntities[2]);

        foreach ($issues as $i => $name) {
            $type = $typeEntities[$i % count($typeEntities)];

            $issue = (new Issue())
                ->setName($name)
                ->setEquipmentType($type)
                ->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($issue);
            $issueEntities[] = $issue;
            $this->addReference('issue.dynamic_' . ($i + 1), $issue);
        }

        $this->addReference('issue.screen', $issueEntities[0]);
        $this->addReference('issue.battery', $issueEntities[1]);
        $this->addReference('issue.keyboard', $issueEntities[5]);

        $manager->flush();
    }
}