<?php
    function form_adduser($hidden = false, $after = true, $header = "", $curuser = NULL) {
        global $userattr, $userdefaults;
        if(is_null($curuser)) $curuser = $userdefaults;
        if ($hidden) { ?>
            <?php if ($header != "") echo "<h3>" . $header . "</h3>"; ?>
            <form action="adduser.php" method="get" autocomplete="off">
                <input type="hidden" name="mail" value="<?php echo $curuser['email']; ?>">
                <input type="hidden" name="user" value="<?php echo $curuser['user']; ?>">
                <input type="hidden" name="level" value="<?php echo $curuser['level']; ?>">
                <input type="hidden" name="sid" value="<?php echo $curuser['sid']; ?>">
                <p><tt>REALLY:</tt> <input type="submit" name="submit" value="Yes, Confirm Submission" style="width: 300px"></p>
            </form>
        <?php } else { ?>
            <?php if ($header != "" && !$after) echo "<h2>" . $header . "</h2>"; ?>
            <p>Here you can add users to the system. You can modify an existing account by entering a valid username or email.</p>
            <?php if ($header != "" && $after) echo "<h2>" . $header . "</h2>"; ?>
            <form action="adduser.php" method="get" autocomplete="off">
                <p><tt>&nbsp;EMAIL:</tt> <input type="text" name="mail" value="<?php echo $curuser['email']; ?>" style="width: 300px"></p>
                <p><tt>&nbsp;&nbsp;USER:</tt> <input type="text" name="user" value="<?php echo $curuser['user']; ?>" style="width: 300px"></p>
                <p><tt>&nbsp;STEAM:</tt> <input type="text" name="sid" value="<?php echo $curuser['sid']; ?>" style="width: 300px"></p>
                <p><tt>&nbsp;LEVEL:</tt>
                    <label class="radiobox"><input type="radio" name="level" value="u" <?php if($curuser['level'] == "u") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel"><?php echo user_text('u'); ?></span></label>
                    <label class="radiobox"><input type="radio" name="level" value="s" <?php if($curuser['level'] == "s") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel"><?php echo user_text('s'); ?></span></label>
                    <label class="radiobox"><input type="radio" name="level" value="m" <?php if($curuser['level'] == "m") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel"><?php echo user_text('m'); ?></span></label>
                    <label class="radiobox"><input type="radio" name="level" value="o" <?php if($curuser['level'] == "o") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel"><?php echo user_text('o'); ?></span></label>
                    <label class="radiobox"><input type="radio" name="level" value="a" <?php if($curuser['level'] == "a") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel"><?php echo user_text('a'); ?></span></label>
                    <label class="radiobox"><input type="radio" name="level" value="d" <?php if($curuser['level'] == "d") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel"><?php echo user_text('d'); ?></span></label>
                    <label class="radiobox"><input type="radio" name="level" value="c" <?php if($curuser['level'] == "c") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel"><?php echo user_text('c'); ?></span></label>
                </p>
                <p><tt>SUBMIT:</tt> <input type="submit" value="Check Details" style="width: 300px"></p>
            </form>
        <?php }
    }
    function form_deluser($hidden = false, $after = true, $header = "", $curuser = NULL) {
        global $userdefaults;
        if(is_null($curuser)) $curuser = $userdefaults;
        if ($hidden) { ?>
            <?php if ($header != "") echo "<h3>" . $header . "</h3>"; ?>
            <form action="deluser.php" method="get" autocomplete="off">
                <input type="hidden" name="mail" value="<?php echo $curuser['email']; ?>">
                <p><tt>REALLY:</tt> <input type="submit" name="submit" value="Yes, Confirm Submission" style="width: 300px"></p>
            </form>
        <?php } else { ?>
            <?php if ($header != "" && !$after) echo "<h2>" . $header . "</h2>"; ?>
            <p>Here you can delete users from the system.</p>
            <?php if ($header != "" && $after) echo "<h2>" . $header . "</h2>"; ?>
            <form action="deluser.php" method="get" autocomplete="off">
                <p><tt>&nbsp;EMAIL:</tt> <input type="text" name="mail" value="<?php echo $curuser['email']; ?>" style="width: 300px"></p>
                <p><tt>SUBMIT:</tt> <input type="submit" value="Check Details" style="width: 300px"></p>
            </form>
        <?php }
    }
?>
