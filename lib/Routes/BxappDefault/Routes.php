<?php
// App::setBundleProtector(['checkDomain]);

App::route()->post('/logger/', 'Actions.logger')->name('bxapp-default-logger');
App::route()->get('/session/getData/?{store}', 'Actions.getSessionData')->where(['store' => '.*'])->name('bxapp-default-session-getData');
App::route()->post('/session/updateData/', 'Actions.updateSessionData')->name('bxapp-default-session-updateData');
App::route()->get('/session/removeData/?{store}', 'Actions.removeSessionData')->where(['store' => '.*'])->name('bxapp-default-session-removeData');
