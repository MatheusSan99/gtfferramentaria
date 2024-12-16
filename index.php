<?php
// Inclui a biblioteca Dompdf
require 'vendor/autoload.php';

use Dompdf\Dompdf;

// Variáveis simuladas de dados (em produção, usar banco de dados)
$dados = [
    ['pv' => '3023', 'imagem' => 'imagemTeste.png', 'nome_peca' => 'FRONT UPPER FASCIA', 'data_t0' => '20/12/2015', 'cliente' => 'STELLANTIS', 'transformador' => 'PLASCAR'],
    ['pv' => '4127', 'imagem' => 'imagemTeste2.png', 'nome_peca' => 'FRONT LOWER FASCIA', 'data_t0' => '01/02/2016', 'cliente' => 'Matheus', 'transformador' => 'PLASCAR'],
    // Adicione mais dados aqui
];

// Captura os filtros via POST
$periodo_inicio = $_POST['periodo_inicio'] ?? '';
$periodo_fim = $_POST['periodo_fim'] ?? '';
$clientes_selecionados = $_POST['clientes'] ?? [];

// Filtra os dados (exemplo básico)
$dados_filtrados = array_filter($dados, function ($item) use ($periodo_inicio, $periodo_fim, $clientes_selecionados) {
    $data_item = DateTime::createFromFormat('d/m/Y', $item['data_t0']);
    $inicio = $periodo_inicio ? new DateTime($periodo_inicio) : null;
    $fim = $periodo_fim ? new DateTime($periodo_fim) : null;

    // Filtro por data
    if ($inicio && $data_item < $inicio) return false;
    if ($fim && $data_item > $fim) return false;

    // Filtro por cliente
    if (!empty($clientes_selecionados) && !in_array($item['cliente'], $clientes_selecionados)) return false;

    return true;
});

// Exporta para PDF
if (isset($_POST['exportar_pdf'])) {
    $dompdf = new Dompdf();
    ob_start();
    include 'tabela_pdf.php'; 
    $html = ob_get_clean();
    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("relatorio.pdf", ["Attachment" => true]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Moldes</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>
<body>
<div class="container">
    <h2 class="my-4">Gestão de Moldes</h2>
    <form method="post">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label>Período de Início</label>
                <input type="date" name="periodo_inicio" class="form-control datepicker" value="<?= htmlspecialchars($periodo_inicio) ?>">
            </div>
            <div class="form-group col-md-3">
                <label>Período de Fim</label>
                <input type="date" name="periodo_fim" class="form-control datepicker" value="<?= htmlspecialchars($periodo_fim) ?>">
            </div>
            <div class="form-group col-md-3">
                <label>Clientes</label>
                <select name="clientes[]" class="form-control" multiple>
                    <?php
                    $clientes = array_column($dados, 'cliente');
                    $clientes = array_unique($clientes);
                    foreach ($clientes as $cliente): ?>
                        <option value="<?= htmlspecialchars($cliente) ?>" <?= in_array($cliente, $clientes_selecionados) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cliente) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" name="exibir_relatorio" class="btn btn-primary">Exibir Relatório</button>
        <button type="submit" name="exportar_pdf" class="btn btn-danger">Exportar para PDF</button>
    </form>

    <?php if (!empty($dados_filtrados)): ?>
        <table class="table table-bordered mt-4">
            <thead>
            <tr>
                <th>PV</th>
                <th>IMAGEM</th>
                <th>NOME DA PEÇA</th>
                <th>DATA T0</th>
                <th>CLIENTE</th>
                <th>TRANSFORMADOR</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($dados_filtrados as $linha): ?>
                <tr>
                    <td><?= htmlspecialchars($linha['pv']) ?></td>
                    <td><img src="<?= htmlspecialchars($linha['imagem']) ?>" style="max-width: 250px; max-height: 250px; width: auto; height: auto;"></td>
                    <td><?= htmlspecialchars($linha['nome_peca']) ?></td>
                    <td><?= htmlspecialchars($linha['data_t0']) ?></td>
                    <td><?= htmlspecialchars($linha['cliente']) ?></td>
                    <td><?= htmlspecialchars($linha['transformador']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="mt-4">Nenhum dado encontrado com os filtros aplicados.</p>
    <?php endif; ?>
</div>

<script>
    $(function () {
        $(".datepicker").datepicker({dateFormat: 'yy-mm-dd'});
    });
</script>
</body>
</html>
