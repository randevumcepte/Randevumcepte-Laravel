$ErrorActionPreference="Stop"
$root="c:\Users\ferdi\Desktop\randevumcepteuygulamaweb\Randevumcepte-Laravel"
$log=Join-Path $root "_render.log"
"BASLADI $([DateTime]::Now.ToString('HH:mm:ss'))" | Set-Content $log -Encoding UTF8
function L($m){ Add-Content $log $m -Encoding UTF8 }

try {
  Add-Type -AssemblyName System.Runtime.WindowsRuntime
  $wrx=[System.WindowsRuntimeSystemExtensions]
  $asOp = ($wrx.GetMethods() | Where-Object { $_.Name -eq 'AsTask' -and $_.GetParameters().Count -eq 1 -and $_.GetParameters()[0].ParameterType.Name -eq 'IAsyncOperation`1' })[0]
  $asAct = ($wrx.GetMethods() | Where-Object { $_.Name -eq 'AsTask' -and $_.GetParameters().Count -eq 1 -and $_.GetParameters()[0].ParameterType.Name -eq 'IAsyncAction' })[0]
  function AwaitOp($op,$t){ $m=$asOp.MakeGenericMethod($t); $task=$m.Invoke($null,@($op)); if(-not $task.Wait(30000)){ throw "timeout op" }; $task.Result }
  function AwaitAct($op){ $task=$asAct.Invoke($null,@($op)); if(-not $task.Wait(30000)){ throw "timeout act" } }

  [Windows.Storage.StorageFile,Windows.Storage,ContentType=WindowsRuntime]|Out-Null
  [Windows.Storage.Streams.InMemoryRandomAccessStream,Windows.Storage.Streams,ContentType=WindowsRuntime]|Out-Null
  [Windows.Storage.Streams.DataReader,Windows.Storage.Streams,ContentType=WindowsRuntime]|Out-Null
  [Windows.Data.Pdf.PdfDocument,Windows.Data.Pdf,ContentType=WindowsRuntime]|Out-Null
  [Windows.Data.Pdf.PdfPageRenderOptions,Windows.Data.Pdf,ContentType=WindowsRuntime]|Out-Null

  $pdfPath=Join-Path $root "Sozlesme-Satis-Hizmet-v2.pdf"
  L "PDF: $pdfPath"
  $sf=AwaitOp ([Windows.Storage.StorageFile]::GetFileFromPathAsync($pdfPath)) ([Windows.Storage.StorageFile])
  $pdf=AwaitOp ([Windows.Data.Pdf.PdfDocument]::LoadFromFileAsync($sf)) ([Windows.Data.Pdf.PdfDocument])
  $pc=$pdf.PageCount
  L "Sayfa: $pc"

  $media=Join-Path $root "_img_build\word\media"
  $relsDir=Join-Path $root "_img_build\word\_rels"
  $rootRels=Join-Path $root "_img_build\_rels"
  $wordDir=Join-Path $root "_img_build\word"
  if(Test-Path (Join-Path $root "_img_build")){ Remove-Item (Join-Path $root "_img_build") -Recurse -Force }
  New-Item -ItemType Directory -Path $media -Force|Out-Null
  New-Item -ItemType Directory -Path $relsDir -Force|Out-Null
  New-Item -ItemType Directory -Path $rootRels -Force|Out-Null

  for($i=0;$i -lt $pc;$i++){
    $page=$pdf.GetPage([uint32]$i)
    $opts=New-Object Windows.Data.Pdf.PdfPageRenderOptions
    $opts.DestinationWidth=[uint32]2480
    $stream=New-Object Windows.Storage.Streams.InMemoryRandomAccessStream
    AwaitAct ($page.RenderToStreamAsync($stream,$opts))
    $size=[uint32]$stream.Size
    $reader=New-Object Windows.Storage.Streams.DataReader($stream.GetInputStreamAt(0))
    AwaitOp ($reader.LoadAsync($size)) ([uint32]) | Out-Null
    $bytes=New-Object byte[] $size
    $reader.ReadBytes($bytes)
    $reader.Dispose(); $stream.Dispose(); $page.Dispose()
    $png=Join-Path $media ("page{0}.png" -f ($i+1))
    [System.IO.File]::WriteAllBytes($png,$bytes)
    L ("page{0}.png yazildi {1} bytes" -f ($i+1),$bytes.Length)
  }

  # ---- OOXML kur ----
  $enc=New-Object System.Text.UTF8Encoding($false)
  $cx=7560000; $cy=10692000
  $body=''
  $relsImg=''
  for($i=1;$i -le $pc;$i++){
    $rid="rIdImg$i"
    $anchor='<w:p><w:pPr><w:spacing w:before="0" w:after="0" w:line="0" w:lineRule="auto"/></w:pPr><w:r><w:drawing>'+
      '<wp:anchor distT="0" distB="0" distL="0" distR="0" simplePos="0" relativeHeight="0" behindDoc="1" locked="0" layoutInCell="1" allowOverlap="1">'+
      '<wp:simplePos x="0" y="0"/>'+
      '<wp:positionH relativeFrom="page"><wp:posOffset>0</wp:posOffset></wp:positionH>'+
      '<wp:positionV relativeFrom="page"><wp:posOffset>0</wp:posOffset></wp:positionV>'+
      '<wp:extent cx="'+$cx+'" cy="'+$cy+'"/>'+
      '<wp:effectExtent l="0" t="0" r="0" b="0"/><wp:wrapNone/>'+
      '<wp:docPr id="'+$i+'" name="Sayfa'+$i+'"/>'+
      '<wp:cNvGraphicFramePr><a:graphicFrameLocks noChangeAspect="1"/></wp:cNvGraphicFramePr>'+
      '<a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'+
      '<pic:pic><pic:nvPicPr><pic:cNvPr id="'+$i+'" name="page'+$i+'.png"/><pic:cNvPicPr/></pic:nvPicPr>'+
      '<pic:blipFill><a:blip r:embed="'+$rid+'"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'+
      '<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="'+$cx+'" cy="'+$cy+'"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'+
      '</pic:pic></a:graphicData></a:graphic></wp:anchor></w:drawing></w:r></w:p>'
    $body+=$anchor
    if($i -lt $pc){ $body+='<w:p><w:r><w:br w:type="page"/></w:r></w:p>' }
    $relsImg+='<Relationship Id="'+$rid+'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/page'+$i+'.png"/>'
  }
  $sectPr='<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="0" w:right="0" w:bottom="0" w:left="0" w:header="0" w:footer="0" w:gutter="0"/></w:sectPr>'
  $docXml='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'+
    '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '+
    'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '+
    'xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" '+
    'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" '+
    'xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'+
    '<w:body>'+$body+$sectPr+'</w:body></w:document>'
  [System.IO.File]::WriteAllText((Join-Path $wordDir "document.xml"),$docXml,$enc)

  $ct='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>'
  [System.IO.File]::WriteAllText((Join-Path $root "_img_build\[Content_Types].xml"),$ct,$enc)
  $rr='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>'
  [System.IO.File]::WriteAllText((Join-Path $rootRels ".rels"),$rr,$enc)
  $dr='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'+$relsImg+'</Relationships>'
  [System.IO.File]::WriteAllText((Join-Path $relsDir "document.xml.rels"),$dr,$enc)

  # ---- zip (forward slash) ----
  Add-Type -AssemblyName System.IO.Compression
  Add-Type -AssemblyName System.IO.Compression.FileSystem
  $docx=Join-Path $root "Randevumcepte-Sozlesme.docx"
  if(Test-Path $docx){ try{ Remove-Item $docx -Force }catch{ $docx=Join-Path $root "Randevumcepte-Sozlesme-2.docx"; if(Test-Path $docx){Remove-Item $docx -Force} } }
  $fs=[System.IO.File]::Open($docx,[System.IO.FileMode]::Create)
  $zip=New-Object System.IO.Compression.ZipArchive($fs,[System.IO.Compression.ZipArchiveMode]::Create)
  function AddE($z,$name,$path){ $e=$z.CreateEntry($name,[System.IO.Compression.CompressionLevel]::Optimal); $s=$e.Open(); $b=[System.IO.File]::ReadAllBytes($path); $s.Write($b,0,$b.Length); $s.Dispose() }
  $stage=Join-Path $root "_img_build"
  AddE $zip "[Content_Types].xml" (Join-Path $stage "[Content_Types].xml")
  AddE $zip "_rels/.rels" (Join-Path $stage "_rels\.rels")
  AddE $zip "word/document.xml" (Join-Path $stage "word\document.xml")
  AddE $zip "word/_rels/document.xml.rels" (Join-Path $stage "word\_rels\document.xml.rels")
  for($i=1;$i -le $pc;$i++){ AddE $zip ("word/media/page{0}.png" -f $i) (Join-Path $stage ("word\media\page{0}.png" -f $i)) }
  $zip.Dispose(); $fs.Dispose()
  L "DOCX: $docx  ($((Get-Item $docx).Length) bytes)"
  L "BITTI OK -> $docx"
} catch {
  L "HATA: $($_.Exception.Message)"
  L $_.ScriptStackTrace
}
