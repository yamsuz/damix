<form action="{url 'auth~auth:in'}" method="post" id="loginForm">
  <fieldset>
	  <table>
			<tr>
				<th><label for="login">{locale 'auth~lclauth.user.login'}</label></th>
				<td><input type="text" name="login" id="login" /></td>
			</tr>
			<tr>
				<th><label for="password">{locale 'auth~lclauth.user.password'}</label></th>
				<td><input type="password" name="password" id="password" /></td>
			</tr>
	   </table>
	<input type="submit" value="valider"/>
   </fieldset>
</form>