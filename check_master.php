<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DATA MASTER CHECK ===\n\n";

// Check Packages
echo "PACKAGES:\n";
$packages = \App\Models\Package::with('router')->get();
foreach ($packages as $package) {
    $routerName = $package->router ? $package->router->name : 'NO ROUTER';
    echo "- {$package->name} (Speed: {$package->speed}) - Router: {$routerName}\n";
}

echo "\nOLTs:\n";
$olts = \App\Models\Olt::all();
foreach ($olts as $olt) {
    echo "- {$olt->name} ({$olt->ip_address}) - User: {$olt->username}\n";
}

echo "\nVLANs:\n";
$vlans = \App\Models\Vlan::all();
foreach ($vlans as $vlan) {
    echo "- VLAN {$vlan->vlan_id} - {$vlan->name}\n";
}

echo "\n=== END CHECK ===\n";
?>