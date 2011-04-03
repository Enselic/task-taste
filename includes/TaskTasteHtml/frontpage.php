    <div class="content-container">
        <div id="logo">
             <div id="logo-text">
                 <h1>Task Taste</h1>
             </div>
             <div id="oneline-description">
                 <p><?php echo TASKTASTE_ONELINE_DESCRIPTION ?></p>
             </div>
        </div>

        <div id="fronpage-container" class="content-container">
            <div id="create-account">
                <form action="/you-need-to-enable-javascript.php" method="post">
                    <h2>Create Account</h2>
                    <p>
                        Your email is only used for password recovery.
                    </p>
                    <table>
                        <tr>
                            <td>Username:</td>
                            <td><input class="focusfirst" name="newusername" type="text" title="Username" value="" autocomplete="off"/></td>
                        </tr>
                        <tr>
                            <td>Password:</td>
                            <td><input name="newpassword" type="password" title="Password" value="" autocomplete="off"/></td>
                        </tr>
                        <tr>
                            <td>Email:</td>
                            <td><input name="newemail" type="text" title="Email" value="" autocomplete="off"/> (optional)</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit" value="Create account" /></td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>

    <?php include 'TaskTasteHtml/bottombar.php' ?>
