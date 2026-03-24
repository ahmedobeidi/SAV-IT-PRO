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
            'Phone', 'Laptop', 'Tablet', 'Desktop PC', 'Gaming Console',
            'Smartwatch', 'Printer', 'Monitor', 'Router', 'NAS',
            'Camera', 'Projector', 'POS Terminal', 'Barcode Scanner', 'TV',
            'Home Theater', 'Drone', 'Server', 'Mini PC', 'E-Reader',
            'VR Headset', 'Scanner', 'Audio Mixer', 'Microphone', 'Speaker',
            'Headphones', 'Smart Home Hub', 'Doorbell Camera', 'Alarm Panel', 'Access Control Unit',
            'Cash Register', 'Label Printer', 'Kiosk', 'Thin Client', 'Docking Station',
            'External SSD', 'Network Switch', 'Firewall Appliance', 'WiFi Access Point', 'UPS',
            'MacBook', 'iMac', 'Workstation', 'Medical Tablet', 'Education Chromebook',
            'Industrial Terminal', 'Touchscreen Panel', 'Attendance Terminal', 'Intercom', 'VoIP Phone'
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
            'Broken Screen', 'Battery Failure', 'Charging Port Damage', 'No Power',
            'Water Damage', 'Keyboard Issue', 'Trackpad Not Working', 'Overheating',
            'Fan Noise', 'WiFi Connectivity Issue', 'Bluetooth Failure', 'Speaker Distortion',
            'Microphone Failure', 'Camera Not Working', 'System Not Booting', 'Blue Screen',
            'Slow Performance', 'Storage Failure', 'RAM Error', 'Motherboard Fault',
            'Power Button Broken', 'Touchscreen Unresponsive', 'Printer Jam', 'Poor Print Quality',
            'Scanner Not Detected', 'Paper Feed Issue', 'Cartridge Error', 'Display Flicker',
            'Dead Pixels', 'HDMI Port Fault', 'USB Port Fault', 'Ethernet Port Failure',
            'Firmware Corruption', 'OS Reinstallation Needed', 'BIOS Issue', 'Login Problem',
            'Audio Jack Failure', 'Hinge Damage', 'Backlight Problem', 'Face ID Failure',
            'Fingerprint Reader Fault', 'GPS Not Working', 'SIM Detection Issue', 'Network Drop',
            'Router Reboot Loop', 'NAS Disk Error', 'Overcurrent Fault', 'Touch Panel Crack',
            'Button Stuck', 'Unknown Intermittent Failure'
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