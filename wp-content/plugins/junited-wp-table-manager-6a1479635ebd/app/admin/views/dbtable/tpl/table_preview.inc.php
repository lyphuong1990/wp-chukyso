<p><?php echo __('Preview the 10 first result rows', 'wptm'); ?></p>
<table>
    
    <thead>
        <tr>
        <?php foreach( $headers as $header ){ ?>
            <th><?php echo $header ?></th>
        <?php }?>
        </tr>
    </thead>
    
    <tbody>
        <?php foreach( $result as $i=>$row ){ ?>
        <tr class="<?php if($i%2==0) { echo 'odd';} ?>">
            <?php foreach( $row as $cell ){ ?>
                <td><?php echo $cell ?></td>
            <?php } ?>
        </tr>
        <?php } ?>
    </tbody>
    
</table>