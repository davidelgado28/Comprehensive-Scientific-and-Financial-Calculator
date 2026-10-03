<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Função utilitária para sanitização de entradas do usuário
 */
function sanitize_input($data) {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Função para formatação de valores monetários
 */
function format_money($value) {
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

$active_tab = isset($_POST['active_tab']) ? sanitize_input($_POST['active_tab']) : 'juros';
$jc_principal = isset($_POST['jc_principal']) ? (float)$_POST['jc_principal'] : 1000.00;
$jc_taxa = isset($_POST['jc_taxa']) ? (float)$_POST['jc_taxa'] : 1.00;
$jc_tipo_taxa = isset($_POST['jc_tipo_taxa']) ? sanitize_input($_POST['jc_tipo_taxa']) : 'mensal';
$jc_tempo = isset($_POST['jc_tempo']) ? (int)$_POST['jc_tempo'] : 12;
$jc_tipo_tempo = isset($_POST['jc_tipo_tempo']) ? sanitize_input($_POST['jc_tipo_tempo']) : 'meses';
$jc_aporte = isset($_POST['jc_aporte']) ? (float)$_POST['jc_aporte'] : 100.00;
$jc_resultado = null;
$cb_numero = isset($_POST['cb_numero']) ? strtoupper(sanitize_input($_POST['cb_numero'])) : '';
$cb_origem = isset($_POST['cb_origem']) ? (int)$_POST['cb_origem'] : 10;
$cb_destino = isset($_POST['cb_destino']) ? (int)$_POST['cb_destino'] : 2;
$cb_resultado = null;
$cb_erro = null;
$bh_a = isset($_POST['bh_a']) ? (float)$_POST['bh_a'] : 1;
$bh_b = isset($_POST['bh_b']) ? (float)$_POST['bh_b'] : -5;
$bh_c = isset($_POST['bh_c']) ? (float)$_POST['bh_c'] : 6;
$bh_resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($active_tab === 'juros') {
        $meses_totais = ($jc_tipo_tempo === 'anos') ? $jc_tempo * 12 : $jc_tempo;
        
        if ($jc_tipo_taxa === 'anual') {
            $taxa_mensal = pow(1 + ($jc_taxa / 100), 1 / 12) - 1;
        } else {
            $taxa_mensal = $jc_taxa / 100;
        }

        $saldo_atual = $jc_principal;
        $total_investido = $jc_principal;
        $tabela_evolucao = [];

        for ($mes = 1; $mes <= $meses_totais; $mes++) {
            $saldo_inicial = $saldo_atual;
            $juros_do_mes = $saldo_inicial * $taxa_mensal;
            
            $aporte = $jc_aporte;
            $saldo_final = $saldo_inicial + $juros_do_mes + $aporte;
            
            $total_investido += $aporte;
            $saldo_atual = $saldo_final;

            $tabela_evolucao[] = [
                'mes' => $mes,
                'saldo_inicial' => $saldo_inicial,
                'aporte' => $aporte,
                'juros' => $juros_do_mes,
                'saldo_final' => $saldo_final
            ];
        }

        $total_juros = $saldo_atual - $total_investido;

        $jc_resultado = [
            'total_investido' => $total_investido,
            'total_juros' => $total_juros,
            'montante_final' => $saldo_atual,
            'evolucao' => $tabela_evolucao
        ];
    }
    if ($active_tab === 'bases') {
        if (!empty($cb_numero)) {
            $valid_patterns = [
                2  => '/^[0-1]+$/',
                8  => '/^[0-7]+$/',
                10 => '/^[0-9]+$/',
                16 => '/^[0-9A-FA-f]+$/'
            ];

            if (isset($valid_patterns[$cb_origem]) && preg_match($valid_patterns[$cb_origem], $cb_numero)) {
                $decimal = base_convert($cb_numero, $cb_origem, 10);
                $cb_resultado = strtoupper(base_convert($decimal, 10, $cb_destino));
            } else {
                $cb_erro = "O valor digitado não é válido para a base de origem selecionada.";
            }
        } else {
            $cb_erro = "Por favor, insira um número para conversão.";
        }
    }

    if ($active_tab === 'bhaskara') {
        if ($bh_a == 0) {
            $bh_resultado = [
                'erro' => 'O coeficiente "a" deve ser diferente de zero para ser uma equação do 2º grau.'
            ];
        } else {
            $delta = ($bh_b * $bh_b) - (4 * $bh_a * $bh_c);
            
            if ($delta < 0) {
                $bh_resultado = [
                    'delta' => $delta,
                    'tipo' => 'sem_raizes',
                    'mensagem' => 'Delta negativo. A equação não possui raízes reais (as raízes pertencem ao conjunto dos números complexos).'
                ];
            } elseif ($delta == 0) {
                $x = -$bh_b / (2 * $bh_a);
                $bh_resultado = [
                    'delta' => $delta,
                    'tipo' => 'raiz_unica',
                    'mensagem' => 'Delta igual a zero. A equação possui duas raízes reais e iguais.',
                    'x1' => $x,
                    'x2' => $x
                ];
            } else {
                $x1 = (-$bh_b + sqrt($delta)) / (2 * $bh_a);
                $x2 = (-$bh_b - sqrt($delta)) / (2 * $bh_a);
                $bh_resultado = [
                    'delta' => $delta,
                    'tipo' => 'duas_raizes',
                    'mensagem' => 'Delta positivo. A equação possui duas raízes reais e distintas.',
                    'x1' => $x1,
                    'x2' => $x2
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suíte Científica & Financeira</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen py-10 px-4 font-sans">

    <div class="max-w-5xl mx-auto">
        <header class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-indigo-400 tracking-tight sm:text-4xl">
                Calculadora Científica & Financeira
            </h1>
            <p class="mt-2 text-slate-400 text-sm sm:text-base">
                Aplicação monolítica construída em PHP Puro com estilização moderna.
            </p>
        </header>

        <div class="flex border-b border-slate-700 mb-8 justify-center space-x-2 sm:space-x-4">
            <button onclick="setTab('juros')" id="btn-juros" 
                class="py-3 px-4 text-sm sm:text-base font-semibold border-b-2 transition-colors duration-200 <?php echo $active_tab === 'juros' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-200'; ?>">
                Juros Compostos
            </button>
            <button onclick="setTab('bases')" id="btn-bases" 
                class="py-3 px-4 text-sm sm:text-base font-semibold border-b-2 transition-colors duration-200 <?php echo $active_tab === 'bases' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-200'; ?>">
                Conversor de Bases
            </button>
            <button onclick="setTab('bhaskara')" id="btn-bhaskara" 
                class="py-3 px-4 text-sm sm:text-base font-semibold border-b-2 transition-colors duration-200 <?php echo $active_tab === 'bhaskara' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-200'; ?>">
                Equação do 2º Grau
            </button>
        </div>

        <div id="tab-juros" class="<?php echo $active_tab === 'juros' ? 'block' : 'hidden'; ?>">
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 shadow-xl mb-8">
                <form action="" method="POST">
                    <input type="hidden" name="active_tab" value="juros">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Valor Inicial (R$)</label>
                            <input type="number" step="0.01" name="jc_principal" value="<?php echo $jc_principal; ?>" required
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Aporte Mensal (R$)</label>
                            <input type="number" step="0.01" name="jc_aporte" value="<?php echo $jc_aporte; ?>"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Taxa de Juros (%)</label>
                            <div class="flex space-x-2">
                                <input type="number" step="0.01" name="jc_taxa" value="<?php echo $jc_taxa; ?>" required
                                    class="w-2/3 bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-indigo-500">
                                <select name="jc_tipo_taxa" class="w-1/3 bg-slate-900 border border-slate-700 rounded-lg px-2 py-2.5 text-slate-100 focus:outline-none focus:border-indigo-500">
                                    <option value="mensal" <?php echo $jc_tipo_taxa === 'mensal' ? 'selected' : ''; ?>>% a.m.</option>
                                    <option value="anual" <?php echo $jc_tipo_taxa === 'anual' ? 'selected' : ''; ?>>% a.a.</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Período</label>
                            <div class="flex space-x-2">
                                <input type="number" name="jc_tempo" value="<?php echo $jc_tempo; ?>" required min="1"
                                    class="w-2/3 bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-indigo-500">
                                <select name="jc_tipo_tempo" class="w-1/3 bg-slate-900 border border-slate-700 rounded-lg px-2 py-2.5 text-slate-100 focus:outline-none focus:border-indigo-500">
                                    <option value="meses" <?php echo $jc_tipo_tempo === 'meses' ? 'selected' : ''; ?>>Meses</option>
                                    <option value="anos" <?php echo $jc_tipo_tempo === 'anos' ? 'selected' : ''; ?>>Anos</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="mt-6 w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        Calcular Simulador
                    </button>
                </form>
            </div>

            <?php if ($jc_resultado): ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
                        <span class="text-xs font-semibold uppercase text-slate-400">Total Investido</span>
                        <p class="text-2xl font-bold text-slate-100 mt-1"><?php echo format_money($jc_resultado['total_investido']); ?></p>
                    </div>
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
                        <span class="text-xs font-semibold uppercase text-slate-400">Total em Juros</span>
                        <p class="text-2xl font-bold text-emerald-400 mt-1"><?php echo format_money($jc_resultado['total_juros']); ?></p>
                    </div>
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-5">
                        <span class="text-xs font-semibold uppercase text-slate-400">Montante Final</span>
                        <p class="text-2xl font-bold text-indigo-400 mt-1"><?php echo format_money($jc_resultado['montante_final']); ?></p>
                    </div>
                </div>

                <div class="bg-slate-800 border border-slate-700 rounded-xl overflow-hidden shadow-xl">
                    <div class="p-4 border-b border-slate-700">
                        <h3 class="font-bold text-lg text-slate-200">Evolução Mensal Detalhada</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-900/50 text-slate-400 uppercase text-xs">
                                    <th class="p-3 border-b border-slate-700">Mês</th>
                                    <th class="p-3 border-b border-slate-700">Saldo Inicial</th>
                                    <th class="p-3 border-b border-slate-700">Aporte</th>
                                    <th class="p-3 border-b border-slate-700">Juros do Mês</th>
                                    <th class="p-3 border-b border-slate-700">Saldo Final</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50">
                                <?php foreach ($jc_resultado['evolucao'] as $row): ?>
                                    <tr class="hover:bg-slate-700/30 transition-colors">
                                        <td class="p-3 font-semibold text-slate-300"><?php echo $row['mes']; ?></td>
                                        <td class="p-3 text-slate-300"><?php echo format_money($row['saldo_inicial']); ?></td>
                                        <td class="p-3 text-slate-300"><?php echo format_money($row['aporte']); ?></td>
                                        <td class="p-3 text-emerald-400"><?php echo format_money($row['juros']); ?></td>
                                        <td class="p-3 font-semibold text-slate-100"><?php echo format_money($row['saldo_final']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div id="tab-bases" class="<?php echo $active_tab === 'bases' ? 'block' : 'hidden'; ?>">
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 shadow-xl">
                <form action="" method="POST">
                    <input type="hidden" name="active_tab" value="bases">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Número para Conversão</label>
                            <input type="text" name="cb_numero" value="<?php echo $cb_numero; ?>" required placeholder="Ex: 1010, FF, 25"
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-indigo-500 uppercase">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Base de Origem</label>
                            <select name="cb_origem" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="10" <?php echo $cb_origem === 10 ? 'selected' : ''; ?>>Decimal (10)</option>
                                <option value="2" <?php echo $cb_origem === 2 ? 'selected' : ''; ?>>Binário (2)</option>
                                <option value="8" <?php echo $cb_origem === 8 ? 'selected' : ''; ?>>Octal (8)</option>
                                <option value="16" <?php echo $cb_origem === 16 ? 'selected' : ''; ?>>Hexadecimal (16)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Base de Destino</label>
                            <select name="cb_destino" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-indigo-500">
                                <option value="2" <?php echo $cb_destino === 2 ? 'selected' : ''; ?>>Binário (2)</option>
                                <option value="10" <?php echo $cb_destino === 10 ? 'selected' : ''; ?>>Decimal (10)</option>
                                <option value="8" <?php echo $cb_destino === 8 ? 'selected' : ''; ?>>Octal (8)</option>
                                <option value="16" <?php echo $cb_destino === 16 ? 'selected' : ''; ?>>Hexadecimal (16)</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="mt-6 w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        Converter Base
                    </button>
                </form>

                <?php if ($cb_erro): ?>
                    <div class="mt-6 p-4 bg-red-900/40 border border-red-500/50 rounded-lg text-red-200 text-sm">
                        <?php echo $cb_erro; ?>
                    </div>
                <?php endif; ?>

                <?php if ($cb_resultado !== null && !$cb_erro): ?>
                    <div class="mt-6 p-6 bg-slate-900 rounded-lg border border-slate-700 text-center">
                        <span class="text-xs font-semibold uppercase text-slate-400">Resultado Convertido</span>
                        <div class="text-3xl font-extrabold text-emerald-400 mt-2 font-mono">
                            <?php echo $cb_resultado; ?>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            Entrada: <span class="font-mono"><?php echo $cb_numero; ?></span> (Base <?php echo $cb_origem; ?>) ➔ Saída: (Base <?php echo $cb_destino; ?>)
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div id="tab-bhaskara" class="<?php echo $active_tab === 'bhaskara' ? 'block' : 'hidden'; ?>">
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 shadow-xl">
                <form action="" method="POST">
                    <input type="hidden" name="active_tab" value="bhaskara">
                    
                    <p class="text-sm text-slate-400 mb-4 text-center">Formato: ax² + bx + c = 0</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Coeficiente (a)</label>
                            <input type="number" step="any" name="bh_a" value="<?php echo $bh_a; ?>" required
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Coeficiente (b)</label>
                            <input type="number" step="any" name="bh_b" value="<?php echo $bh_b; ?>" required
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Coeficiente (c)</label>
                            <input type="number" step="any" name="bh_c" value="<?php echo $bh_c; ?>" required
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-100 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <button type="submit" class="mt-6 w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-4 rounded-lg transition duration-200">
                        Calcular Equação
                    </button>
                </form>

                <?php if ($bh_resultado): ?>
                    <div class="mt-6 p-6 bg-slate-900 rounded-lg border border-slate-700">
                        <?php if (isset($bh_resultado['erro'])): ?>
                            <p class="text-red-400 text-sm font-semibold"><?php echo $bh_resultado['erro']; ?></p>
                        <?php else: ?>
                            <div class="mb-4 pb-4 border-b border-slate-800">
                                <span class="text-xs uppercase text-slate-400 font-semibold">Valor do Delta (&Delta;)</span>
                                <p class="text-2xl font-bold text-indigo-400 font-mono mt-1">
                                    &Delta; = <?php echo $bh_resultado['delta']; ?>
                                </p>
                                <p class="text-sm text-slate-300 mt-2"><?php echo $bh_resultado['mensagem']; ?></p>
                            </div>

                            <?php if ($bh_resultado['tipo'] !== 'sem_raizes'): ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="p-4 bg-slate-800 rounded border border-slate-700">
                                        <span class="text-xs text-slate-400 font-semibold">Raiz X1</span>
                                        <p class="text-xl font-bold text-emerald-400 font-mono mt-1">
                                            <?php echo number_format($bh_resultado['x1'], 4, ',', '.'); ?>
                                        </p>
                                    </div>
                                    <div class="p-4 bg-slate-800 rounded border border-slate-700">
                                        <span class="text-xs text-slate-400 font-semibold">Raiz X2</span>
                                        <p class="text-xl font-bold text-emerald-400 font-mono mt-1">
                                            <?php echo number_format($bh_resultado['x2'], 4, ',', '.'); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function setTab(tabName) {
            document.getElementById('tab-juros').classList.add('hidden');
            document.getElementById('tab-bases').classList.add('hidden');
            document.getElementById('tab-bhaskara').classList.add('hidden');

            const buttons = ['btn-juros', 'btn-bases', 'btn-bhaskara'];
            buttons.forEach(id => {
                const btn = document.getElementById(id);
                btn.classList.remove('border-indigo-500', 'text-indigo-400');
                btn.classList.add('border-transparent', 'text-slate-400');
            });
            document.getElementById('tab-' + tabName).classList.remove('hidden');

            const activeBtn = document.getElementById('btn-' + tabName);
            activeBtn.classList.add('border-indigo-500', 'text-indigo-400');
            activeBtn.classList.remove('border-transparent', 'text-slate-400');
        }
    </script>
</body>
</html>
