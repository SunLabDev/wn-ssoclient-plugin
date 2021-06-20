## SSO Clients
This plugin allows your website to act as an SSO Client:
- It will grant the authorization to access an SSO Provider userbase
- It will display a button on login form to login without providing any credential
> Note: This plugin is intended to be used along with [this SSO Provider](https://github.com/sunlabdev/wn-ssoprovider-plugin)

### Composer installation
```terminal
composer require sunlab/wn-ssoclient-plugin
```

### Components
This plugin provides one component:
- SSOLoginButton: Displays the SSO login button
