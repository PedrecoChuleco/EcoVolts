<?php
/**
 * Gerador de PDF mínimo, em PHP puro (sem Composer e sem bibliotecas externas).
 *
 * Recursos: páginas A4, texto (Helvetica e Helvetica-Bold), linhas, retângulos,
 * cores, quebra de linha automática e numeração de páginas. É o suficiente para
 * documentos como o orçamento; não suporta imagens nem fontes customizadas.
 *
 * Requer a extensão mbstring (já vem ativada no XAMPP).
 *
 * COORDENADAS: em pontos (1 pt = 1/72 pol). A origem é o canto SUPERIOR esquerdo
 * da página e o eixo Y cresce para baixo (mais intuitivo que o padrão do PDF).
 * Para texto, $y é a linha de base.
 *
 * Uso:
 *   $pdf = new PdfSimples();
 *   $pdf->texto(40, 60, 'Olá', 14, true);
 *   $pdf->novaPagina();
 *   $pdf->numerarPaginas();
 *   echo $pdf->saida();
 */
final class PdfSimples
{
    public const LARGURA = 595.28;   // A4
    public const ALTURA  = 841.89;

    /** Largura dos caracteres ASCII 32..126 na Helvetica (unidades de 1/1000 do corpo). */
    private const LARGURAS = [
        278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278,
        556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556,
        1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778,
        667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 278, 278, 278, 469, 556,
        333, 556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556,
        556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584,
    ];

    /** @var string[] conteúdo (operadores gráficos) de cada página */
    private array $paginas = [];
    private int $atual = -1;

    public function __construct()
    {
        $this->novaPagina();
    }

    public function novaPagina(): void
    {
        $this->paginas[] = '';
        $this->atual = count($this->paginas) - 1;
    }

    public function totalPaginas(): int
    {
        return count($this->paginas);
    }

    // ------------------------------------------------------------------ desenho

    /** Escreve uma linha de texto. $cor = [r, g, b] de 0 a 255. */
    public function texto(float $x, float $y, string $texto, float $tamanho = 10, bool $negrito = false, array $cor = [33, 33, 33]): void
    {
        $this->paginas[$this->atual] .= sprintf(
            "BT /F%d %.2F Tf %s %.2F %.2F Td (%s) Tj ET\n",
            $negrito ? 2 : 1,
            $tamanho,
            $this->cor($cor),
            $x,
            self::ALTURA - $y,
            $this->converter($texto)
        );
    }

    /** Texto alinhado à direita: $xDireita é onde o texto termina. */
    public function textoDireita(float $xDireita, float $y, string $texto, float $tamanho = 10, bool $negrito = false, array $cor = [33, 33, 33]): void
    {
        $this->texto($xDireita - $this->larguraTexto($texto, $tamanho, $negrito), $y, $texto, $tamanho, $negrito, $cor);
    }

    public function linha(float $x1, float $y1, float $x2, float $y2, array $cor = [216, 225, 219], float $espessura = 0.6): void
    {
        $this->paginas[$this->atual] .= sprintf(
            "q %s %.2F w %.2F %.2F m %.2F %.2F l S Q\n",
            $this->cor($cor, true),
            $espessura,
            $x1,
            self::ALTURA - $y1,
            $x2,
            self::ALTURA - $y2
        );
    }

    /** Retângulo. $yTopo é a borda superior. Informe $preenchimento e/ou $borda. */
    public function retangulo(float $x, float $yTopo, float $largura, float $altura, ?array $preenchimento = null, ?array $borda = null, float $espessura = 0.6): void
    {
        if ($preenchimento === null && $borda === null) {
            return;
        }

        $ops = '';
        if ($preenchimento !== null) {
            $ops .= $this->cor($preenchimento) . ' ';
        }
        if ($borda !== null) {
            $ops .= $this->cor($borda, true) . sprintf(' %.2F w ', $espessura);
        }
        $pintar = $preenchimento !== null ? ($borda !== null ? 'B' : 'f') : 'S';

        $this->paginas[$this->atual] .= sprintf(
            "q %s%.2F %.2F %.2F %.2F re %s Q\n",
            $ops,
            $x,
            self::ALTURA - $yTopo - $altura,
            $largura,
            $altura,
            $pintar
        );
    }

    // ------------------------------------------------------------------ medidas

    /** Largura aproximada do texto em pontos (métricas da Helvetica). */
    public function larguraTexto(string $texto, float $tamanho = 10, bool $negrito = false): float
    {
        $soma = 0;
        foreach (mb_str_split($texto) as $caractere) {
            $codigo = mb_ord($caractere);
            $soma += ($codigo >= 32 && $codigo <= 126) ? self::LARGURAS[$codigo - 32] : 556;
        }

        return $soma * $tamanho / 1000 * ($negrito ? 1.06 : 1.0);
    }

    /** Corta o texto com reticências para caber em $largura pontos. */
    public function ajustar(string $texto, float $largura, float $tamanho = 10, bool $negrito = false): string
    {
        if ($this->larguraTexto($texto, $tamanho, $negrito) <= $largura) {
            return $texto;
        }

        while ($texto !== '' && $this->larguraTexto($texto . '...', $tamanho, $negrito) > $largura) {
            $texto = mb_substr($texto, 0, -1);
        }

        return rtrim($texto) . '...';
    }

    /** Quebra o texto em linhas que caibam em $largura pontos. */
    public function quebrar(string $texto, float $largura, float $tamanho = 10, bool $negrito = false): array
    {
        $linhas = [];
        $atual  = '';

        foreach (preg_split('/\s+/u', trim($texto)) ?: [] as $palavra) {
            $teste = $atual === '' ? $palavra : $atual . ' ' . $palavra;
            if ($atual !== '' && $this->larguraTexto($teste, $tamanho, $negrito) > $largura) {
                $linhas[] = $atual;
                $atual = $palavra;
            } else {
                $atual = $teste;
            }
        }
        if ($atual !== '') {
            $linhas[] = $atual;
        }

        return $linhas;
    }

    /** Escreve um parágrafo com quebra automática e devolve o Y logo abaixo dele. */
    public function paragrafo(float $x, float $y, float $largura, string $texto, float $tamanho = 9, bool $negrito = false, array $cor = [33, 33, 33], float $entrelinha = 1.4): float
    {
        foreach ($this->quebrar($texto, $largura, $tamanho, $negrito) as $linha) {
            $this->texto($x, $y, $linha, $tamanho, $negrito, $cor);
            $y += $tamanho * $entrelinha;
        }

        return $y;
    }

    /** Escreve "Página X de Y" no rodapé de todas as páginas. Chame depois de montar o documento. */
    public function numerarPaginas(float $margemDireita = 40, float $y = 820): void
    {
        $total    = $this->totalPaginas();
        $original = $this->atual;

        foreach (array_keys($this->paginas) as $indice) {
            $this->atual = $indice;
            $this->textoDireita(self::LARGURA - $margemDireita, $y, 'Página ' . ($indice + 1) . ' de ' . $total, 8, false, [110, 120, 114]);
        }

        $this->atual = $original;
    }

    // -------------------------------------------------------------------- saída

    /** Monta o arquivo PDF e devolve seus bytes. */
    public function saida(): string
    {
        $objetos = [];
        $objetos[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        // 2 (Pages) é preenchido depois que soubermos os ids das páginas.
        $objetos[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objetos[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $filhas = [];
        foreach ($this->paginas as $indice => $conteudo) {
            $idPagina   = 5 + $indice * 2;
            $idConteudo = $idPagina + 1;
            $filhas[]   = $idPagina . ' 0 R';

            $objetos[$idPagina] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                self::LARGURA,
                self::ALTURA,
                $idConteudo
            );
            $objetos[$idConteudo] = '<< /Length ' . strlen($conteudo) . " >>\nstream\n" . $conteudo . "endstream";
        }
        $objetos[2] = '<< /Type /Pages /Kids [' . implode(' ', $filhas) . '] /Count ' . count($filhas) . ' >>';
        ksort($objetos);

        $pdf = "%PDF-1.4\n";
        $posicoes = [];
        foreach ($objetos as $numero => $corpo) {
            $posicoes[$numero] = strlen($pdf);
            $pdf .= $numero . " 0 obj\n" . $corpo . "\nendobj\n";
        }

        $inicioXref = strlen($pdf);
        $total = count($objetos) + 1;
        $pdf .= "xref\n0 {$total}\n0000000000 65535 f \n";
        foreach ($posicoes as $posicao) {
            $pdf .= sprintf("%010d 00000 n \n", $posicao);
        }
        $pdf .= "trailer\n<< /Size {$total} /Root 1 0 R >>\nstartxref\n{$inicioXref}\n%%EOF";

        return $pdf;
    }

    // ----------------------------------------------------------------- internos

    /** UTF-8 -> Windows-1252 (WinAnsi) com escape dos caracteres especiais do PDF. */
    private function converter(string $texto): string
    {
        $convertido = mb_convert_encoding($texto, 'Windows-1252', 'UTF-8');

        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $convertido);
    }

    private function cor(array $rgb, bool $traco = false): string
    {
        return sprintf('%.3F %.3F %.3F %s', $rgb[0] / 255, $rgb[1] / 255, $rgb[2] / 255, $traco ? 'RG' : 'rg');
    }
}
