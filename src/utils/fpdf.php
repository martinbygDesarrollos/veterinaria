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
			), $category, $fecha);
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

    function Row($data, $category, $fecha)
    {
        // Calculate the height of the row
        $nb = 0;
        for($i=0;$i<count($data);$i++)
            $nb = max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h = 5*$nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h, $category, $fecha);
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

    function RowInt($data, $category, $fecha)
    {
        // Calculate the height of the row
        $nb = 0;
        for($i=0;$i<count($data);$i++)
            $nb = max($nb,$this->NbLines($this->widthsInt[$i],$data[$i]));
        $h = 5*$nb;
        // Issue a page break first if needed
        $this->CheckPageBreak($h, $category, $fecha);
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

    function CheckPageBreak($h, $category,$fecha)
    {
        // If the height h would cause an overflow, add a new page immediately
        if($this->file->GetY()+$h>$this->PageBreakTrigger){
            $this->footer();
            $this->file->AddPage($this->file->CurOrientation);

            $categoryName = $this->categoryTitle($category);
            $this->encabezado($fecha, $categoryName);

            $this->file->SetFontSize(10);

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
                ), "internacion", $fecha);

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
                $this->encabezado($fecha, $categoryName);
            }

            $this->file->SetFontSize(12);
            $this->file->Cell(0,10,iconv("UTF-8", "windows-1252",$row['usuario']." ".date("d/m/y", strtotime($row['fecha']))." ".date("H:i", strtotime($row['fecha'].$row['hora'])) ),0,2,'R');
            $this->file->SetFontSize(12);
            $this->RowHist(["Temperatura","FC.","FR.","TLLC. (seg)"], [47.5,47.5,47.5,47.5]);
            $this->RowHist([$row['temperatura'],$row['fc'],$row['fr'],$row['tllc']], [47.5,47.5,47.5,47.5]);
            $this->file->SetFontSize(12);
            $this->MultiCellHist(0,7,iconv("UTF-8", "windows-1252","Motivo: ".$row['motivoConsulta'] ),0,'L', false, $fecha, $categoryName);
            $this->file->SetFontSize(12);
            $this->MultiCellHist(0,7, iconv("UTF-8", "windows-1252","Observaciones: ".$row['observaciones'] ),0,'L', false, $fecha, $categoryName);
            $this->file->SetFontSize(12);
            $this->MultiCellHist(0,7, iconv("UTF-8", "windows-1252","Tratamiento: ".$row['diagnostico']),0,'L', false, $fecha, $categoryName);
        }

        $this->footer();
        $nameFile = $categoryName."_".date("Ymd");

        $this->file->Output("F","imprimibles/$nameFile.pdf");
        $response->result = 2;
        $response->name = $nameFile;
        return $response;
    }



    function MultiCellHist($w, $h, $txt, $border=0, $align='J', $fill=false, $fecha, $categoryName)
    {
        // Output text with automatic or explicit line breaks
        if(!isset($this->file->CurrentFont))
            $this->file->Error('No font has been set');
        $cw = &$this->file->CurrentFont['cw'];
        if($w==0)
            $w = $this->file->w-$this->file->rMargin-$this->file->x;
        $wmax = ($w-2*$this->file->cMargin)*1000/$this->file->FontSize;
        $s = str_replace("\r",'',$txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $b = 0;
        if($border)
        {
            if($border==1)
            {
                $border = 'LTRB';
                $b = 'LRT';
                $b2 = 'LR';
            }
            else
            {
                $b2 = '';
                if(strpos($border,'L')!==false)
                    $b2 .= 'L';
                if(strpos($border,'R')!==false)
                    $b2 .= 'R';
                $b = (strpos($border,'T')!==false) ? $b2.'T' : $b2;
            }
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $ns = 0;
        $nl = 1;
        while($i<$nb)
        {
            if($this->file->GetY() +51 > $this->file->PageBreakTrigger){
                $this->file->SetAutoPageBreak(1, 1);
                $this->footer();
                $this->file->AddPage($this->file->CurOrientation);
                $this->encabezado($fecha, $categoryName);
                $this->file->SetFontSize(12);

            }
            // Get next character
            $c = $s[$i];
            if($c=="\n")
            {
                // Explicit line break
                if($this->file->ws>0)
                {
                    $this->file->ws = 0;
                    $this->file->_out('0 Tw');
                }
                $this->file->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if($border && $nl==2)
                    $b = $b2;
                continue;
            }
            if($c==' ')
            {
                $sep = $i;
                $ls = $l;
                $ns++;
            }
            $l += $cw[$c];
            if($l>$wmax)
            {
                // Automatic line break
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                    if($this->file->ws>0)
                    {
                        $this->file->ws = 0;
                        $this->file->_out('0 Tw');
                    }
                    $this->file->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
                }
                else
                {
                    if($align=='J')
                    {
                        $this->file->ws = ($ns>1) ? ($wmax-$ls)/1000*$this->file->FontSize/($ns-1) : 0;
                        $this->file->_out(sprintf('%.3F Tw',$this->file->ws*$this->file->k));
                    }
                    $this->file->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
                    $i = $sep+1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $ns = 0;
                $nl++;
                if($border && $nl==2)
                    $b = $b2;
            }
            else
                $i++;
        }
        // Last chunk
        if($this->file->ws>0)
        {
            $this->file->ws = 0;
            $this->file->_out('0 Tw');
        }
        if($border && strpos($border,'B')!==false)
            $b .= 'B';
        $this->file->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
        $this->file->x = $this->file->lMargin;
    }


    function petMedicineDocument($listMedicine){

        $response = new stdClass();
        $fecha = date("d/m/y");

        $this->file = new Fpdf("P", "mm", "A4");
        $this->file->AddPage();
        $this->file->SetAutoPageBreak(1, 1);
        $this->file->SetFont('helvetica','',10);


        $this->file->Image('..\public\img\inicio.jpeg',10,10,30);
        $this->file->SetFontSize(18);
        $this->file->setXY(10,14);
        $this->file->Cell(0,10,iconv("UTF-8", "windows-1252", $title),0,2,'C');

        $this->file->SetFontSize(10);
        $this->file->setXY(10,16);
        $this->file->Cell(0,10,$date,0,2,'R');

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
            ), $category, $fecha);
        }
        $this->footer();
        $nameFile = $categoryName."_".$date;

        $this->file->Output("F","imprimibles/$nameFile.pdf");
        $response->result = 2;
        $response->name = $nameFile;
        return $response;

    }

}
	
?>