Form listener for Symfony
========================

Easy form handling in Symfony controllers.

Why?

- use really thin controllers
- make your controller unit-testable easier
- Do not Repeat Yourself implementing the boring form-subbmission code!

All you need is to use the @Form class annotation, for example:

`@Form("new_form",method="createCreateForm")`

With this annotation you can list all the forms used in current controller. The `method` attribute points to method that creates this particular form.

Now, you can annotate methods which "starts" form-flow with the `@FormStarter` and the methods which accepts form submission using the `@FormAcceptor` annotation.

The FormListener will handle the flow, it will create the form and inject it into the template parameters in form-starter methods, so you don't have to do it manually.
Also, it will handle the acceptor; bind and validate the form. Note that the form-acceptor method is executed only if the form is valid, so the only thing you need to do in your form-acceptor method is to persist you fresh created/updated entity.
If the form submission fails, the flow stops at starter method - it will show the bound form with error message.

You can also "decorate" the form-acceptor behavior for failed forms. 
Use the `rejector` property to point a method which will be executed when form submission fails. This may be useful when you need to show some flash-messages or use some logging.

See the [`PostController`](Resources/example/Controller/PostController.php) class for example how to use those annotations. If you want to see how it works, check out the [example symfony-project](https://github.com/thanek/easy-forms-bundle-example) that uses this bundle.

*Note that you need to use the `@Template` annotations in your form-starter controller, because FormListener needs to act before the view is rendered.* 

## Installation

You need to add the following to your `composer.json` file:

```
    "repositories": [
        {
            "url": "https://github.com/thanek/easy-forms-bundle.git",
            "type": "git"
        }
    ],
```

in the `require` section, add:

```
    "xis/easy-forms-bundle": "~0.1"
```

Don't forget to update composer dependencies:

```
composer.phar update
```

Then you need to update your `AppKernel.php` file and enable the bundle by adding the folowing entry to the `$bundles` array in `registerBundles` method:

```
new Xis\EasyFormsBundle\XisEasyFormsBundle(),
```

And that's it.

