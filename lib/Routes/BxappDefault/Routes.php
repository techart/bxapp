<?php
// App::setBundleProtector(['checkDomain]);

App::route()->post('/logger/', 'Actions.logger')->name('bxapp-default-logger');
App::route()->get('/session/getData/?{store}', 'Actions.getSessionData')->name('bxapp-session-getData')->where(['store' => '.*']);
App::route()->post('/session/updateData/', 'Actions.updateSessionData')->name('bxapp-session-updateData');
App::route()->get('/session/removeData/?{store}', 'Actions.removeSessionData')->name('bxapp-session-removeData')->where(['store' => '.*']);
App::route()->get('/session/createNextSession/', 'Actions.createNextSession')->name('bxapp-session-createNextSession');
App::route()->get('/session/checkNextSession/', 'Actions.checkNextSession')->name('bxapp-session-checkNextSession');
