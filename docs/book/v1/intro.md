# ACL Authorizations for Expressive

This component provides [Access Control List](https://en.wikipedia.org/wiki/Access_control_list)
(ACL) authorization abstraction for the [zend-expressive-authorization](https://github.com/zendframework/zend-expressive-authentication)
library.

ACL is based around the idea of **resources** and **roles**:

- a **resource** is an object to which access is controlled;
- a **role** is an object that may request access to a resource.

Put simply, roles request access to resources. For example, if a parking
attendant requests access to a car, then the parking attendant is the requesting
role, and the car is the resource, since access to the car may not be granted to
everyone.

Through the specification and use of an ACL, an application may control how
roles are granted access to resources. For instance, in a web application a
*resource* can be a page, a portion of a view, a route, etc. A *role* can be,
for instance, a user's role of a registered users or a client identity of a web
API call.

## Configure an ACL system

You can configure your ACL using a configuration file, as follows:

```php
// config/autoload/authorization.local.php
return [
    // ...
    'zend-expressive-authorization-acl' => [
        'roles' => [
            'administrator' => [],
            'editor'        => ['administrator'],
            'contributor'   => ['editor'],
        ],
        'resources' => [
            'admin.dashboard',
            'admin.posts',
            'admin.publish',
            'admin.settings'
        ],
        'allow' => [
            'administrator' => ['admin.settings'],
            'contributor' => [
                'admin.dashboard',
                'admin.posts',
            ],
            'editor' => [
                'admin.publish'
            ]
        ]
    ]
];
```

This example is the same used in the documentation of [zend-expressive-authorization-rbac](https://docs.zendframework.com/zend-expressive-authorization-rbac/v1/intro/#configure-an-rbac-system).
We have three roles: *administrator*, *editor*, and *contributor* for a blog
web site. The *administrator* has the higher level of authorization (no parent).
A *contributor* has the permission to create a post and manage the dashboard
(parent *administrator*). Finally, an *editor* can only create or update a
post (parent *editor*).

The resources, in this case are the route name to be accessed. By default, all
the resources are denied, unless otherwise stated. In our example, we allow
the route `admin.settings` for the *administrator*, the routes `admin.dashboard`
and `admin.posts` for the *contributor* and the route `admin.publish` for the
*editor*. Because the *contributor* inherits permissions from *editor* he/she will
have access to `admin.publish` route. The same for *administrator*, that
inherits permissions from *contributor* so he/she will have access to all the
routes.

You can also deny a resource using the `deny` key in the configuration file.
For instance, you can deny the access of the route `admin.dashboard` to
*administrator* adding the following configuration in the previous example:

```php
return [
    // ...
    'zend-expressive-authorization-acl' => [
        // previous configuration array
        'deny' => [
            'administrator' => ['admin.dashboard']
        ]
    ]
]
```

The usage of `allow` and `deny` can help to configure complex permission
scenarios including or excluding specific authorizations.

As we did in [zend-expressive-authorization-rbac](https://github.com/zendframework/zend-expressive-authorization-rbac),
the default implementation uses route name as permissions. If you want to change
the permissions type and the logic for authorization, you need to provide a New
implementation of the [Zend\Expressive\Authorization\AuthorizationInterface](https://github.com/zendframework/zend-expressive-authorization/blob/master/src/AuthorizationInterface.php) interface.

> Zend-expressive-authorization-acl uses [zend-permissions-acl](https://github.com/zendframework/zend-permissions-acl)
> to implement an ACL system. For more information we suggest to read the
> [documentation](https://docs.zendframework.com/zend-permissions-acl/) of this
> library.
