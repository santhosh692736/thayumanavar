<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PDF extends FPDF_Protection{
	function RotatedText($x,$y,$txt,$angle)
	{
	    //Text rotated around its origin
	    $this->Rotate($angle,$x,$y);
	    $this->Text($x,$y,$txt);
	    $this->Rotate(0);
	}

	public function WordWrap(&$text, $maxwidth)
	{
		
    $text = trim($text);
    if ($text==='')
        return 0;
    $space = $this->GetStringWidth(' ');
    $lines = explode("\n", $text);
    $text = '';
    $count = 0;

    foreach ($lines as $line)
    {
        $words = preg_split('/ +/', $line);
        $width = 0;

        foreach ($words as $word)
        {
            $wordwidth = $this->GetStringWidth($word);
            if ($wordwidth > $maxwidth)
            {
                // Word is too long, we cut it
                for($i=0; $i<strlen($word); $i++)
                {
                    $wordwidth = $this->GetStringWidth(substr($word, $i, 1));
                    if($width + $wordwidth <= $maxwidth)
                    {
                        $width += $wordwidth;
                        $text .= substr($word, $i, 1);
                    }
                    else
                    {
                        $width = $wordwidth;
                        $text = rtrim($text)."\n".substr($word, $i, 1);
                        $count++;
                    }
                }
            }
            elseif($width + $wordwidth <= $maxwidth)
            {
                $width += $wordwidth + $space;
                $text .= $word.' ';
            }
            else
            {
                $width = $wordwidth + $space;
                $text = rtrim($text)."\n".$word.' ';
                $count++;
            }
        }
        $text = rtrim($text)."\n";
        $count++;
    }
    $text = rtrim($text);
    return $count;
	}

	function RotatedImage($file,$x,$y,$w,$h,$angle)
	{
	    //Image rotated around its upper-left corner
	    $this->Rotate($angle,$x,$y);
	    $this->Image($file,$x,$y,$w,$h);
	    $this->Rotate(0);
	}

	// Simple table
	function BasicTable($header, $data)
	{
	    // Header
	    foreach($header as $col)
	        $this->Cell(40,7,$col,1);
	    $this->Ln();
	    // Data
	    foreach($data as $row)
	    {
	        foreach($row as $col)
	        $this->Cell(40,6,$col,1);
	        $this->Ln();
	    }
	}


	// Better table
	function ImprovedTable($header, $data)
	{
	    // Column widths
	    $w = array(40, 35, 40, 45);
	    // Header
	    for($i=0;$i<count($header);$i++)
	        $this->Cell($w[$i],7,$header[$i],1,0,'C');
	    $this->Ln();
	    // Data
	    foreach($data as $row)
	    {
	        $this->Cell($w[0],6,$row[0],'LR');
	        $this->Cell($w[1],6,$row[1],'LR');
	        $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R');
	        $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R');
	        $this->Ln();
	    }
	    // Closing line
	    $this->Cell(array_sum($w),0,'','T');
	}

	// Colored table
	function FancyTable($header, $data)
	{
	    // Colors, line width and bold font
	    $this->SetFillColor(255,0,0);
	    $this->SetTextColor(255);
	    $this->SetDrawColor(128,0,0);
	    $this->SetLineWidth(.3);
	    $this->SetFont('','B');
	    // Header
	    $w = array(40, 35, 40, 45);
	    for($i=0;$i<count($header);$i++)
	        $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
	    $this->Ln();
	    // Color and font restoration
	    $this->SetFillColor(224,235,255);
	    $this->SetTextColor(0);
	    $this->SetFont('');
	    // Data
	    $fill = false;
	    foreach($data as $row)
	    {
	        $this->Cell($w[0],6,$row[0],'LR',0,'L',$fill);
	        $this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
	        $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
	        $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);
	        $this->Ln();
	        $fill = !$fill;
	    }
	    // Closing line
	    $this->Cell(array_sum($w),0,'','T');
	}
	// Colored table
	function FancyTable2($header, $data, $w)
	{
		// Colors, line width and bold font
	    $this->SetFillColor(52, 152, 219);
	    $this->SetTextColor(255);
	    $this->SetDrawColor(128,0,0);
	    $this->SetLineWidth(.3);
	    $this->SetFont('','B', 9);
	    // Header
	    for($i=0;$i<count($header);$i++)
	        $this->Cell($w[$i],5,$header[$i],1,0,'C',true);
	    $this->Ln();
	    // Color and font restoration
	    $this->SetFillColor(224,235,255);
	    $this->SetTextColor(0);
	    $this->SetFont('','',9);
	    // Data
	    $fill = false;
	    foreach($data as $row)
	    {

	        for($i=0;$i<count($header);$i++){
	        	$fill = false;
	        	$this->Cell($w[$i],5,$row[$i],'LR',0,'L',$fill);
	        }
	        /*$this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
	        $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
	        $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);*/
	        $this->Ln();
	        $fill = !$fill;
	    }
	    // Closing line
	    $this->Cell(array_sum($w),0,'','T');
	    $this->Ln();
	}
	//
	function reportTable($header, $data, $w, $align, $tableTitle = '')
	{
	    // Colors, line width and bold font
	    $this->SetFillColor(46,80,144);
	    $this->SetTextColor(255);
	    $this->SetDrawColor(0,0,0);
	    $this->SetLineWidth(.2);
	    $this->SetFont('','B', 9);
	    //
	    // Header
	    for($i=0;$i<count($header);$i++)
	        $this->Cell($w[$i],5,$header[$i],1,0,$align[$i],true);
	    $this->Ln();
	    // Color and font restoration
	    $this->SetFillColor(224,235,255);
	    $this->SetTextColor(0);
	    $this->SetFont('','',9);
	    // Data
	    $fill = false;
	    $lastKeyId =count($header);
	    $sno=1;
	    $lastCnt = count($data)-5;
	    foreach($data as $row)
	    {
	    	$value = 0;
	    	if(array_key_exists($lastKeyId, $row)){
	    		$value = $row[$lastKeyId];
	    	
		    	$fill = false;
		    	if($tableTitle=="user" || $tableTitle=="spuser" || $tableTitle=="exclodecolor"){
		    		$fill = false;
			    	$this->SetTextColor(0);
			    	$this->SetFont('','',9);
			    	//	
		    	}else{
			    	//
			    	if($value==0){
						//Red
		    			$this->SetFillColor(255,203,203);
				    	$this->SetTextColor(0);
				    	$this->SetFont('','',9);
				    	$fill = true;
		    		}
		    		elseif($value>0){
		    			if($sno<=5){
		    				//Green
				    		$this->SetFillColor(221,234,209);
				    		$this->SetTextColor(0);
				    		$this->SetFont('','',9);
				    		$fill = true;	
		    			}
		    			elseif($sno>=$lastCnt){
		    				//Yellow
			    			$this->SetFillColor(255,231,161);
					    	$this->SetTextColor(0);
					    	$this->SetFont('','',9);
					    	$fill = true;		
		    			}else{
				    		$fill = false;
				    		$this->SetTextColor(0);
			    			$this->SetFont('','',9);
				    	}
				    }
				}
			}else{
				//Footer
				$this->SetFillColor(46,80,144);
				$this->SetTextColor(255);
				$this->SetFont('','',9);
				$fill = true;	
				if($tableTitle=="spuser"){
		    		$fill = false;
			    	$this->SetTextColor(0);
			    	$this->SetFont('','',9);
			    	//	
		    	}
			}	    	
		    //
		    if(array_key_exists("colspanTitle", $row)){
		    	$this->SetFillColor(179, 240, 255);
				$this->SetTextColor(0);
				$this->SetFont('','',9);
				$fill = true;
		    	$this->Cell(280,6,$row["colspanTitle"],'LR',0,'C',$fill);
		    }
		    else{
		    	for($i=0;$i<count($header);$i++){
		    		if(array_key_exists($i,$row)){
		    			$bvalue = $row[$i];
		    		}else{
		    			$bvalue = "i".$i;
		    		}
		    		//$this->Cell($w[$i],5,$row[$i],'LR',0,$align[$i],$fill);
		        	$this->Cell($w[$i],5,$bvalue,1,0,$align[$i],$fill);
		        }
		        /*$this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
		        $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
		        $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);*/
		    }    
	        $this->Ln();
	        $fill = !$fill;
	        $sno++;
	    }
	    // Closing line
	    $this->Cell(array_sum($w),0,'','T');
	    $this->Ln();
	}

	function reportTable2($header, $data, $w, $align, $colHeader = array(), $bw = array())
	{
	    // Colors, line width and bold font
	    $this->SetFillColor(46,80,144);
	    $this->SetTextColor(255);
	    $this->SetDrawColor(0,0,0);
	    $this->SetLineWidth(.2);
	    $this->SetFont('','B', 9);
	    //
	    $colCount = count($colHeader);
	    if($colCount==0){
	    	$colHeader = $header;
	    	$bw = $w;
	    }
	    //$colHeader = $header;
	    // Header
	    for($i=0;$i<count($header);$i++)
	        $this->Cell($w[$i],5,$header[$i],1,0,$align[$i],true);
	    $this->Ln();
	    if($colCount>0){
	    	for($i=0;$i<count($colHeader);$i++)
        		$this->Cell($bw[$i],5,$colHeader[$i],1,0,$align[$i],true);
	    	$this->Ln();
	    }
	    // Color and font restoration
	    $this->SetFillColor(224,235,255);
	    $this->SetTextColor(0);
	    $this->SetFont('','',9);
	    // Data
	    $fill = false;
	    $lastKeyId =count($colHeader);
	    $sno=1;
	    foreach($data as $row)
	    {
	    	$value = 0;
	    	if(array_key_exists($lastKeyId, $row)){
	    		$value = $row[$lastKeyId];
	    		if($value>0){
		    		$this->SetFillColor(0,102,0);
		    		$this->SetTextColor(255);
		    		$this->SetFont('','',9);
		    		$fill = true;
		    	}else{
		    		$fill = false;
		    		$this->SetTextColor(0);
	    			$this->SetFont('','',9);
		    	}
	    	}else{
	    		//For total - table footer
	    		$this->SetFillColor(224,224,224);
	    		$this->SetTextColor(0);
		    	$this->SetFont('','',9);
		    	$fill = true;
	    	}
	    	for($i=0;$i<count($colHeader);$i++){
	    		if(array_key_exists($i,$row)){
	    			$value = $row[$i];
	    		}else{
	    			$value = "i".$i;
	    		}
	    		$value = "99999998";
	        	//$this->Cell($w[$i],5,$row[$i],'LR',0,$align[$i],$fill);
	        	$this->Cell($bw[$i],5,$value,1,0,$align[$i],$fill);
	        }
	        /*$this->Cell($w[1],6,$row[1],'LR',0,'L',$fill);
	        $this->Cell($w[2],6,number_format($row[2]),'LR',0,'R',$fill);
	        $this->Cell($w[3],6,number_format($row[3]),'LR',0,'R',$fill);*/
	        $this->Ln();
	        $fill = !$fill;
	        $sno++;
	    }
	    // Closing line
	    $this->Cell(array_sum($w),0,'','T');
	    $this->Ln();
	}
}

?>