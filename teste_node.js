import fs from "fs";

const jsonString = fs.readFileSync("json_teste.json", "utf-8");
const data = JSON.parse(jsonString);

function calcTeste(data, nome_produto, cod_pagamento, num_parcelas) {
  let valorComDesconto = null;
  let valorTotalProduto = 0;
  let minParcelas = 0;
  let maxParcelas = 0;
  let parcelamento = [];
  let quantidadeTitulos = 0;

  const produtos = data.dividas_calculadas?.produtos?.produto;

  if (produtos && Array.isArray(produtos)) {
    outerLoop: for (const produto of produtos) {
      if (produto.pro_nom === nome_produto) {
        for (const formaNegociacao of produto.formasNegociacao
          .forma_negociacao) {
          if (cod_pagamento === formaNegociacao.for_cod) {
            minParcelas = parseInt(
              formaNegociacao.regras_acordo.regra_acordo.aco_minnumpar
            );
            maxParcelas = parseInt(
              formaNegociacao.regras_acordo.regra_acordo.aco_maxnumpar
            );

            if (num_parcelas < minParcelas || num_parcelas > maxParcelas) {
              return { mensagem: "Número de parcelas inválido" };
            }

            for (const parcela of formaNegociacao.parcelas.parcela) {

              quantidadeTitulos = formaNegociacao.parcelas.parcela.length

              for (const item of parcela.lancamentos.item) {
                const valor = parseFloat(item.valor);
                valorTotalProduto = parseFloat(valor).toFixed(2);
                const desconto =
                  (valor * parseFloat(item.maximo_desconto)) / 100;
                valorComDesconto = parseFloat(valor - desconto).toFixed(2);

                break outerLoop;
              }
            }
          }
        }
      }
    }
  }

  const totalParcelasComDesconto = parseFloat(valorComDesconto / num_parcelas).toFixed(2);

  parcelamento.push({
    quantidadeMax: maxParcelas,
    valorCadaParcela: parseFloat(valorComDesconto / maxParcelas).toFixed(2),
  });

  return {
    totalParcelasComDesconto,
    valorTotalProduto,
    valorComDesconto,
    parcelamento,
    quantidadeTitulos,
  };
}

const resultado = calcTeste(data, "mb", "432", 1);

console.log(resultado);
