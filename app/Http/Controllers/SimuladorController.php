<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SimuladorController extends Controller
{
    private $dadosSimulador;
    private $simulacao = [];

    public function simular(Request $request)
    {
        $this->carregarArquivoDadosSimulador()
            ->simularEmprestimo($request->valor_emprestimo)
            ->filtrarInstituicao($request->instituicoes)
            ->filtrarConvenio($request->convenios)
            ->filtrarParcelas($request->parcelas);

        return response()->json($this->simulacao);
    }

    private function carregarArquivoDadosSimulador(): self
    {
        $json = File::get(storage_path("app/public/simulador/taxas_instituicoes.json"));
        $this->dadosSimulador = collect(json_decode($json));
        return $this;
    }

    private function simularEmprestimo(float $valorEmprestimo): self
    {
        foreach ($this->dadosSimulador as $dados) {
            $this->simulacao[$dados->instituicao][] = [
                "taxa"          => $dados->taxaJuros,
                "parcelas"      => $dados->parcelas,
                "valor_parcela" => $this->calcularValorDaParcela($valorEmprestimo, $dados->coeficiente),
                "convenio"      => $dados->convenio,
            ];
        }
        return $this;
    }

    private function calcularValorDaParcela(float $valorEmprestimo, float $coeficiente): float
    {
        return round($valorEmprestimo * $coeficiente, 2);
    }

    private function filtrarInstituicao(array $instituicoes): self
    {
        if (count($instituicoes)) {
            $arrayAux = [];
            foreach ($instituicoes as $key => $instituicao) {
                if (array_key_exists($instituicao, $this->simulacao)) {
                    $arrayAux[$instituicao] = $this->simulacao[$instituicao];
                }
            }
            $this->simulacao = $arrayAux;
        }

        return $this;
    }

    private function filtrarConvenio(array $convenios): self
    {
        if (count($convenios)) {
            $arrayAux = [];
            foreach ($this->simulacao as $instituicao => $simulacoes) {
                foreach ($simulacoes as $simulacao) {
                    if (in_array($simulacao['convenio'], $convenios)) {
                        $arrayAux[$instituicao][] = $simulacao;
                    }
                }
            }

            $this->simulacao = $arrayAux;
        }

        return $this;
    }

    private function filtrarParcelas($quantidadeParcelas): self
    {
        if ($quantidadeParcelas) {
            $arrayAux = [];
            foreach ($this->simulacao as $instituicao => $simulacoes) {
                foreach ($simulacoes as $simulacao) {
                    if ($simulacao['parcelas'] == $quantidadeParcelas) {
                        $arrayAux[$instituicao][] = $simulacao;
                    }
                }
            }

            $this->simulacao = $arrayAux;
        }

        return $this;
    }
}
