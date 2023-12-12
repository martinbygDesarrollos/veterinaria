<?php

require_once '../include/pdf/fpdf.php';

/**
 *
 */
class Pdf extends FPDF
{
	protected $file = null;
	protected $heads = array('Hecho','Hora', 'Motivo', 'Cliente', 'Mascota', 'Contacto' );
    protected $widths = array (12, 15, 40, 37, 35, 50);
    protected $aligns;

	function domiciliosDocument($date, $arrayDomicilios)
	{
		$response = new stdClass();
		$fecha = date("d/m/Y", strtotime($date));

		$this->file = new Fpdf("P", "mm", "A4");
		$this->file->AddPage();
		$this->file->SetFont('helvetica','',10);


		$this->encabezado($fecha, "Domicilios");
        $this->file->SetFontSize(10);

        $this->file->setXY(10,30);
        //Line break
        //$this->file->Ln(10);
        for ($i=0; $i<count($this->heads); $i++)
        {
            $this->file->Cell ($this->widths[$i], 10, $this->heads[$i], 1, 0, 'L', 0);
        }


		$this->file->setXY(10,40);

		foreach ($arrayDomicilios as $row) {
			$this->Row(array(
				$row['estado'],
				date("H:i", strtotime($row['fechaHora'])),
				$row['descripcion'],
				$row['idSocio'],
				$row['idMascota'],
				"contactos"
			));
		}

		$this->file->Output("F","imprimibles/domicilios.pdf");
		$response->result = 2;
		$response->name = "domicilios";
		return $response;
	}


	function encabezado($date, $title)
    {
        $this->file->Image('..\public\img\inicio.jpeg',10,10,30);
        $this->file->SetFontSize(18);
        $this->file->setXY(10,10);
        $this->file->Cell(0,10,$title,0,2,'C');

        $this->file->SetFontSize(10);
		$this->file->setXY(10,10);
        $this->file->Cell(0,10,$date,0,2,'R');

    }


    function SetAligns($a)
    {
        // Set the array of column alignments
        $this->aligns = $a;
    }

    function Row($data)
    {
        // Calculate the height of the row
        $nb = 0;
        for($i=0;$i<count($data);$i++)
            $nb = max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h = 5*$nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h);
        // Draw the cells of the row
        for($i=0;$i<count($data);$i++)
        {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Save the current position
            $x = $this->file->GetX();
            $y = $this->file->GetY();
            // Draw the border
            $this->file->Rect($x,$y,$w,$h);
            // Print the text
            $this->file->MultiCell($w,5,$data[$i],0,$a);
            // Put the position to the right of the cell
            $this->file->SetXY($x+$w,$y);
        }
        // Go to the next line
        $this->file->Ln($h);
    }

    function CheckPageBreak($h)
    {
        // If the height h would cause an overflow, add a new page immediately
        if($this->file->GetY()+$h>$this->PageBreakTrigger)
            $this->file->AddPage($this->file->CurOrientation);
    }

    function NbLines($w, $txt)
    {
        // Compute the number of lines a MultiCell of width w will take

		$this->file->SetFont('helvetica','',10);
		$this->SetFont('helvetica','',10);

        if(!isset($this->CurrentFont))
            $this->Error('No font has been set');
        $cw = $this->CurrentFont['cw'];
        if($w==0)
            $w = $this->w-$this->rMargin-$this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r",'',(string)$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while($i<$nb)
        {
            $c = $s[$i];
            if($c=="\n")
            {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep = $i;
            $l += $cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i = $sep+1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }

}
	
?>