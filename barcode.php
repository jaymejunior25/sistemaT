<?php
// barcode.php



class barras extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Código de Barras', 0, 1, 'C');
        $this->Ln(10);
    }

    function Barcode($code)
    {
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, $this->Code39($code), 0, 1, 'C');
        $this->Ln(10);
    }

    function Code39($code)
    {
        $code39 = array(
            '0' => 'N0', '1' => 'N1', '2' => 'N2', '3' => 'N3', '4' => 'N4', '5' => 'N5', '6' => 'N6', '7' => 'N7', '8' => 'N8', '9' => 'N9',
            'A' => 'NA', 'B' => 'NB', 'C' => 'NC', 'D' => 'ND', 'E' => 'NE', 'F' => 'NF', 'G' => 'NG', 'H' => 'NH', 'I' => 'NI', 'J' => 'NJ',
            'K' => 'NK', 'L' => 'NL', 'M' => 'NM', 'N' => 'NN', 'O' => 'NO', 'P' => 'NP', 'Q' => 'NQ', 'R' => 'NR', 'S' => 'NS', 'T' => 'NT',
            'U' => 'NU', 'V' => 'NV', 'W' => 'NW', 'X' => 'NX', 'Y' => 'NY', 'Z' => 'NZ',
            '-' => 'N-', '.' => 'N.', ' ' => 'N ',
            '*' => 'N*', '$' => 'N$', '/' => 'N/', '+' => 'N+'
        );

        $result = '';
        for ($i = 0; $i < strlen($code); $i++) {
            $char = $code[$i];
            if (isset($code39[$char])) {
                $result .= $code39[$char];
            } else {
                $result .= 'N ';
            }
        }
        return $result;
    }
}


?>
