$jabatanMap = \App\Models\Jabatan::pluck("id", "kod")->toArray();
$updated = 0;
foreach (\App\Models\Kontrak::whereNull("jabatan_id")->whereNotNull("no_kontrak")->get() as $kontrak) {
    $parts = explode("/", $kontrak->no_kontrak);
    $prefix = $parts[0] ?? null;
    if ($prefix) {
        if (isset($jabatanMap[$prefix])) {
            $kontrak->jabatan_id = $jabatanMap[$prefix];
            $kontrak->save();
            $updated++;
        }
    }
}
echo "Updated: $updated records.";
