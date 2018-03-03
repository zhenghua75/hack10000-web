项目目录
1. 数据层：Model/UserModel 用于定义数据相关的自动验证和自动完成和数据存取接口
2. 逻辑层：Logic/UserLogic 用于定义用户相关的业务逻辑
3. 服务层：Service/UserService 用于定义用户相关的服务接口等
view 普通视图层目录
mobile 手机端访问视图层目录


Controller/UserController //用于用户的业务逻辑控制和调度
Event/UserEvent //用于用户的事件响应操作
UserEvent 负责内部的事件响应，并且只能在内部调用：
A('User','Event');

