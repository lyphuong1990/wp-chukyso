<?php $r= 0;$c =0; ?>
    <thead>
        <tr>
        <?php foreach( $headers as $header ){  ?>
            <th class="<?php echo 'dtr'.$r.' dtc'.$c ;?>" ><?php echo $header ?></th>
        <?php $c++; }?>
        </tr>
    </thead>
    
    <tbody>
        <?php foreach( $result as $row ){ $r++; $c= 0; ?>
        <tr>
            <?php foreach( $row as $cell ){ ?>
                <td class="<?php echo 'dtr'.$r.' dtc'.$c ;?>"><?php echo $cell ?></td>
            <?php  $c++; } ?>
        </tr>
        <?php } ?>
    </tbody>
