<?php
    function form_adduser($hidden = false, $after = true, $header = "", $usermail = "", $username = "", $userlevel = "") {
        if ($hidden) { ?>
            <?php if ($header != "") echo "<h3>" . $header . "</h3>"; ?>
            <form action="adduser.php" method="get" autocomplete="off">
                <input type="hidden" name="mail" value="<?php echo $usermail; ?>">
                <input type="hidden" name="user" value="<?php echo $username; ?>">
                <input type="hidden" name="level" value="<?php echo $userlevel; ?>">
                <p><tt>REALLY:</tt> <input type="submit" name="submit" value="Confirm Submission"></p>
            </form>
        <?php } else { ?>
            <?php if ($header != "" && !$after) echo "<h2>" . $header . "</h2>"; ?>
            <p>Here you can add users to the system. You can modify a username or level by entering an existing email.</p>
            <?php if ($header != "" && $after) echo "<h2>" . $header . "</h2>"; ?>
            <form action="adduser.php" method="get" autocomplete="off">
                <p><tt>&nbsp;EMAIL:</tt> <input type="text" name="mail" value="<?php echo $usermail; ?>"></p>
                <p><tt>&nbsp;&nbsp;USER:</tt> <input type="text" name="user" value="<?php echo $username; ?>"></p>
                <p><tt>&nbsp;LEVEL:</tt>
                    <span style="display: inline">
                    <label class="radiobox"><input type="radio" name="level" value="u" <?php if($userlevel == "u") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel">User</span></label>
                    <label class="radiobox"><input type="radio" name="level" value="s" <?php if($userlevel == "s") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel">Supporter</span></label>
                    <label class="radiobox"><input type="radio" name="level" value="m" <?php if($userlevel == "m") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel">Moderator</span></label>
                    <label class="radiobox"><input type="radio" name="level" value="o" <?php if($userlevel == "o") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel">Operator</span></label>
                    <br /><tt>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</tt>
                    <label class="radiobox"><input type="radio" name="level" value="a" <?php if($userlevel == "a") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel">Administrator</span></label>
                    <label class="radiobox"><input type="radio" name="level" value="d" <?php if($userlevel == "d") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel">Developer</span></label>
                    <label class="radiobox"><input type="radio" name="level" value="c" <?php if($userlevel == "c") echo "checked" ?>><span class="radiomark"></span><span class="radiolabel">Founder</span></label>
                    </span>
                </p>
                <p><tt>SUBMIT:</tt> <input type="submit" value="Check Submission"></p>
            </form>
        <?php }
    }
    function form_deluser($hidden = false, $after = true, $header = "", $usermail = "") {
        if ($hidden) { ?>
            <?php if ($header != "") echo "<h3>" . $header . "</h3>"; ?>
            <form action="deluser.php" method="get" autocomplete="off">
                <input type="hidden" name="mail" value="<?php echo $usermail; ?>">
                <p><tt>REALLY:</tt> <input type="submit" name="submit" value="Confirm Submission"></p>
            </form>
        <?php } else { ?>
            <?php if ($header != "" && !$after) echo "<h2>" . $header . "</h2>"; ?>
            <p>Here you can delete users from the system.</p>
            <?php if ($header != "" && $after) echo "<h2>" . $header . "</h2>"; ?>
            <form action="deluser.php" method="get" autocomplete="off">
                <p><tt>&nbsp;EMAIL:</tt> <input type="text" name="mail" value="<?php echo $usermail; ?>"></p>
                <p><tt>SUBMIT:</tt> <input type="submit" value="Check Submission"></p>
            </form>
        <?php }
    }
?>
