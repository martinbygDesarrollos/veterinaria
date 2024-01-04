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
    protected $headsInt = array('Mascota',"Dueño", 'Modalidad', 'Contacto' );
    protected $widthsInt = array (47, 47, 47, 47);
    protected $headsHist = array('Vet.','Fecha','Motivo', 'Observaciones','Tratamiento','Temp.','FC.','FR.','TLLC.',);
    protected $widthsHist = array (14,10,42,42,42,10,10,10,10);
    protected $aligns;

	function calendarDocument($date, $category, $arrayData)
	{
		$response = new stdClass();
		$fecha = date("d/m/y", strtotime($date));

		$this->file = new Fpdf("P", "mm", "A4");
		$this->file->AddPage();
        $this->file->SetAutoPageBreak(1, 1);
		$this->file->SetFont('helvetica','',10);


        $categoryName = $this->categoryTitle($category);
        $this->encabezado($fecha, $categoryName);

        $this->file->SetFontSize(10);

        $this->encabezadoTabla();

		foreach ($arrayData as $row) {
			$this->Row(array(
				$row['estado'],
				date("H:i", strtotime($row['fechaHora'])),
				$row['descripcion'],
				$row['idSocio']." - ".$row['socionombre'],
				$row['idMascota']." - ".$row['nombre'],
				$row['telefax']."\n".$row['telefono']."\n".$row['direccion']
			), $category);
		}
        $this->footer();
        $nameFile = $categoryName."_".$date;

		$this->file->Output("F","imprimibles/$nameFile.pdf");
		$response->result = 2;
		$response->name = $nameFile;
		return $response;
	}


	function encabezado($date, $title)
    {
        $this->file->Image('..\public\img\inicio.jpeg',10,10,30);
        $this->file->SetFontSize(18);
        $this->file->setXY(10,14);
        $this->file->Cell(0,10,iconv("UTF-8", "windows-1252", $title),0,2,'C');

        $this->file->SetFontSize(10);
		$this->file->setXY(10,16);
        $this->file->Cell(0,10,$date,0,2,'R');

    }


    function encabezadoTabla(){
        $this->file->setXY(10,30);
        for ($i=0; $i<count($this->heads); $i++)
        {
            $this->file->Cell ($this->widths[$i], 10, iconv("UTF-8", "windows-1252",$this->heads[$i]), 1, 0, 'L', 0);
        }
        $this->file->setXY(10,40);
    }


    function SetAligns($a)
    {
        // Set the array of column alignments
        $this->aligns = $a;
    }

    function Row($data, $category)
    {
        // Calculate the height of the row
        $nb = 0;
        for($i=0;$i<count($data);$i++)
            $nb = max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h = 5*$nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h, $category);
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
            $this->file->MultiCell($w,5,iconv("UTF-8", "windows-1252",$data[$i]),0,$a);
            // Put the position to the right of the cell
            $this->file->SetXY($x+$w,$y);
        }
        // Go to the next line
        $this->file->Ln($h);
    }

    function RowInt($data, $category)
    {
        // Calculate the height of the row
        $nb = 0;
        for($i=0;$i<count($data);$i++)
            $nb = max($nb,$this->NbLines($this->widthsInt[$i],$data[$i]));
        $h = 5*$nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h, $category);
        // Draw the cells of the row
        for($i=0;$i<count($data);$i++)
        {
            $w = $this->widthsInt[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Save the current position
            $x = $this->file->GetX();
            $y = $this->file->GetY();
            // Draw the border
            $this->file->Rect($x,$y,$w,$h);
            // Print the text
            $this->file->MultiCell($w,5,iconv("UTF-8", "windows-1252",$data[$i]),0,$a);
            // Put the position to the right of the cell
            $this->file->SetXY($x+$w,$y);
        }
        // Go to the next line
        $this->file->Ln($h);
    }

    function RowHist($data, $widths)
    {
        // Calculate the height of the row
        $nb = 0;
        for($i=0;$i<count($data);$i++)
            $nb = max($nb,$this->NbLines($widths[$i],$data[$i]));
        $h = 5*$nb;
        // Draw the cells of the row
        for($i=0;$i<count($data);$i++)
        {
            $w = $widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            // Save the current position
            $x = $this->file->GetX();
            $y = $this->file->GetY();
            // Draw the border
            $this->file->Rect($x,$y,$w,$h);
            // Print the text
            $this->file->MultiCell($w,5,iconv("UTF-8", "windows-1252",$data[$i]),0,$a);
            // Put the position to the right of the cell
            $this->file->SetXY($x+$w,$y);
        }
        // Go to the next line
        $this->file->Ln($h);
    }

    function CheckPageBreak($h, $category)
    {
        // If the height h would cause an overflow, add a new page immediately
        if($this->file->GetY()+$h>$this->PageBreakTrigger){
            $this->footer();
            $this->file->AddPage($this->file->CurOrientation);
            $this->file->SetAutoPageBreak(1, 1);

            if ($category === 'internacion') {
                $this->encabezadoTablaInternacion();
            }else{
                $this->encabezadoTabla();
            }

        }
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


    function footer()
    {
        // Go to 1.5 cm from bottom
        $h = $this->file->PageBreakTrigger -10;
        $this->file->SetY($h);
        // Print centered page number
        $this->file->Cell(0, 0, $this->file->PageNo(), 0, 0, 'R');
    }


    function categoryTitle($category){

        switch ($category) {
            case 'domicilios':
                return "Domicilios";
            case 'cirugia':
                return "Cirugías";
            case 'internacion':
                return "Internación";
            case 'historia':
                return "Historia clínica";
            default:
                return "Imprimible";
        }

    }


    function internacionDocument( $data ){

        $response = new stdClass();
        $fecha = date("d/m/y");

        $this->file = new Fpdf("P", "mm", "A4");
        $this->file->AddPage();
        $this->file->SetAutoPageBreak(1, 1);
        $this->file->SetFont('helvetica','',10);


        $categoryName = $this->categoryTitle("internacion");
        $this->encabezado($fecha, $categoryName);

        $this->file->SetFontSize(10);

        $this->encabezadoTablaInternacion();

        foreach ($data as $row) {

            if (isset($row['internado']) && $row['internado'] != "" ){

                $internado = $row['internado'] == "vet" ? "En veterinaria" : "Dar seguimiento";

                $this->RowInt(array(
                    $row['nombre'],
                    $row['nomCliente'],
                    $internado,
                    $row['telefax']
                ), "internacion");

            }
        }
        $this->footer();
        $nameFile = $categoryName."_".date("Ymd");

        $this->file->Output("F","imprimibles/$nameFile.pdf");
        $response->result = 2;
        $response->name = $nameFile;
        return $response;

    }


    function encabezadoTablaInternacion(){
        $this->file->setXY(10,30);
        for ($i=0; $i<count($this->headsInt); $i++)
        {
            $this->file->Cell($this->widthsInt[$i], 10, iconv("UTF-8", "windows-1252",$this->headsInt[$i]), 1, 0, 'L', 0);
        }
        $this->file->setXY(10,40);
    }

    public function petHistoryDocument($data){
        $response = new stdClass();
        $fecha = date("d/m/y");

        $this->file = new Fpdf("P", "mm", "A4");
        $this->file->AddPage();
        //$this->file->SetAutoPageBreak(3, 3);
        $this->file->SetFont('helvetica','',12);


        $categoryName = $this->categoryTitle("historia");
        $this->encabezado($fecha, $categoryName);

        foreach ($data as $row) {
            if($this->file->GetY() +51 > $this->file->PageBreakTrigger){
                $this->file->SetAutoPageBreak(1, 1);
                $this->footer();
                $this->file->AddPage($this->file->CurOrientation);
            }

            $this->file->SetFontSize(12);
            $this->file->Cell(0,10,iconv("UTF-8", "windows-1252",$row['usuario']." ".date("d/m/y", strtotime($row['fecha']))." ".date("H:i", strtotime($row['fecha'].$row['hora'])) ),0,2,'R');
            $this->file->SetFontSize(12);
            $this->RowHist(["Temperatura","FC.","FR.","TLLC. (seg)"], [47.5,47.5,47.5,47.5]);
            $this->RowHist([$row['temperatura'],$row['fc'],$row['fr'],$row['tllc']], [47.5,47.5,47.5,47.5]);
            $this->file->SetFontSize(12);
            $this->file->MultiCell(0,7,iconv("UTF-8", "windows-1252","Motivo: ".$row['motivoConsulta'] ),0,'L');
            $this->file->SetFontSize(12);
            $this->file->MultiCell(0,7, iconv("UTF-8", "windows-1252","Observaciones: ".$row['observaciones'] ),0,'L');
            $this->file->SetFontSize(12);
            $this->file->MultiCell(0,7, iconv("UTF-8", "windows-1252","Tratamiento: ".$row['diagnostico']),0,'L');
        }

        $this->footer();
        $nameFile = $categoryName."_".date("Ymd");

        $this->file->Output("F","imprimibles/$nameFile.pdf");
        $response->result = 2;
        $response->name = $nameFile;
        return $response;
    }

}
	
?>