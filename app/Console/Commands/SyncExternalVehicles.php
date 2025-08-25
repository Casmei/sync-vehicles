<?php

namespace App\Console\Commands;

use App\Services\LoadVehicleService;
use Illuminate\Console\Command;

class SyncExternalVehicles extends Command
{
    protected $signature = 'vehicles:sync';
    protected $description = 'Sincroniza dados com api de veículos externa';

    public function __construct(private readonly LoadVehicleService $loader)
    {
        parent::__construct();
    }

    public function handle(): int
    {

        $url = (string) config('services.alpesone.export');

        if (!$url) {
            $this->fail('Config services.alpesone.export ausente. Defina ALPESONE_EXPORT_URL no .env.');
        }

        $this->newLine();
        $this->info('Fazendo fetch da API externa...');
        $data = $this->loader->fetchExternalVehicles($url);

        if ($data === null) {
            $this->fail('Falha ao acessar a API externa ou payload inválido.');
        }

        $extSig = $this->loader->computeExternalSignature($data);
        $locSig = $this->loader->computeLocalSignature();

        $this->newLine();
        $this->line('Assinaturas:');
        $this->line('  externa → count=' . $extSig['count'] . ' | max_updated=' . $extSig['max_updated']);
        $this->line('  local   → count=' . $locSig['count'] . ' | max_updated=' . $locSig['max_updated']);

        if ($this->loader->signaturesEqual($extSig, $locSig)) {
            $this->newLine();
            $this->info('Sem mudanças — nada a sincronizar.');
            return self::SUCCESS;
        }

        $total = is_countable($data) ? count($data) : 0;
        $this->newLine();
        $this->warn("Mudanças detectadas — sincronizando {$total} registro(s)...");
        $bar = $this->output->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%%  | %elapsed:6s%');

        $bar->start();

        // passa o callback que avança a barra
        $stats = $this->loader->syncExternal($data, function () use ($bar) {
            $bar->advance();
        });

        $bar->finish();
        $this->newLine(2);

        $this->info(sprintf(
            'Sync concluído → created=%d | updated=%d | skipped=%d',
            $stats['created'],
            $stats['updated'],
            $stats['skipped']
        ));

        $this->newLine();
        $this->line('Salvando JSON...');
        $this->loader->persistJsonStable($data);
        $versioned = $this->loader->persistJsonVersioned($data);
        $this->info('JSON salvo em: storage/app/' . LoadVehicleService::JSON_PATH);
        $this->line('Cópia versionada: storage/app/' . $versioned);
        return self::SUCCESS;
    }

}
