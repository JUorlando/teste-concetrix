<?php

$jsonString = file_get_contents("json_teste.json");
$data = json_decode($jsonString, true);

function calcTeste($data, $nome_produto, $cod_pagamento, $num_parcelas) {
    $valorComDesconto = null;
    $valorTotalProduto = 0;
    $minParcelas = 0;
    $maxParcelas = 0;
    $parcelamento = [];
    $quantidadeTitulos = 0;

    $produtos = $data['dividas_calculadas']['produtos']['produto'] ?? null;

    if ($produtos && is_array($produtos)) {
        foreach ($produtos as $produto) {
            if ($produto['pro_nom'] === $nome_produto) {
                foreach ($produto['formasNegociacao']['forma_negociacao'] as $formaNegociacao) {
                    if ($cod_pagamento === $formaNegociacao['for_cod']) {
                        $minParcelas = (int) $formaNegociacao['regras_acordo']['regra_acordo']['aco_minnumpar'];
                        $maxParcelas = (int) $formaNegociacao['regras_acordo']['regra_acordo']['aco_maxnumpar'];

                        if ($num_parcelas < $minParcelas || $num_parcelas > $maxParcelas) {
                            return ['mensagem' => 'Número de parcelas inválido'];
                        }

                        foreach ($formaNegociacao['parcelas']['parcela'] as $parcela) {
                            $quantidadeTitulos = count($formaNegociacao['parcelas']['parcela']);

                            foreach ($parcela['lancamentos']['item'] as $item) {
                                $valor = (float) $item['valor'];
                                $valorTotalProduto = number_format($valor, 2, '.', '');
                                $desconto = ($valor * (float) $item['maximo_desconto']) / 100;
                                $valorComDesconto = number_format($valor - $desconto, 2, '.', '');

                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }

    $totalParcelasComDesconto = number_format($valorComDesconto / $num_parcelas, 2, '.', '');

    $parcelamento[] = [
        'quantidadeMax' => $maxParcelas,
        'valorCadaParcela' => number_format($valorComDesconto / $maxParcelas, 2, '.', '')
    ];

    return [
        'totalParcelasComDesconto' => $totalParcelasComDesconto,
        'valorTotalProduto' => $valorTotalProduto,
        'valorComDesconto' => $valorComDesconto,
        'parcelamento' => $parcelamento,
        'quantidadeTitulos' => $quantidadeTitulos
    ];
}

$resultado = calcTeste($data, "mb", "432", 1);

print_r($resultado);

?>

