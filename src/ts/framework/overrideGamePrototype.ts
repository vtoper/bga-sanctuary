import { logOverride, onLogAdded } from "../format";
import { getRandomId, updatePlayerOrdering } from "./utils";

export const overrideGamePrototype = function(gameui: ExtendedGameGui) {
    gameui._notif_uid_to_log_id = {};
    gameui._notif_uid_to_mobile_log_id = {};
    gameui._last_notif = null;

    gameui.format_string_recursive = function(log: string, args) {
        if (log && args && !args.processed) {
            args.processed = true;
            
            const replaceArgs = (args: object) => {
                for (const arg in args) {
                    if (arg == "_private") {
                        args[arg] = replaceArgs(args[arg]);
                    }

                    if (args.hasOwnProperty(arg)) {
                        if (logOverride.hasOwnProperty(arg)) {
                            args[arg] = logOverride[arg](args);
                        }
                    }
                }

                return args;
            }

            args = replaceArgs(args);
        }
        
        return gameui.constructor.prototype.format_string_recursive.call(this, log, args);
    }

    gameui.updatePlayerOrdering = function() {
        gameui.constructor.prototype.updatePlayerOrdering.call(this);
        updatePlayerOrdering();
    }

    /*
    * [Undocumented] Called by BGA framework on any notification message
    * Handle cancelling log messages for restart turn
    */
    gameui.onPlaceLogOnChannel = function(msg: Notif<GenericArguments>) {
        const currentLogId = this.notifqueue.next_log_id;
        const currentMobileLogId = this.next_log_id;
        const res = gameui.constructor.prototype.onPlaceLogOnChannel.call(this, msg);
        this._notif_uid_to_log_id[msg.uid] = currentLogId;
        this._notif_uid_to_mobile_log_id[msg.uid] = currentMobileLogId;
        this._last_notif = {
            logId: currentLogId,
            mobileLogId: currentMobileLogId,
            msg,
        };
        return res;
    }

    gameui.statusBar.addActionButton = function(label, callback, params = {}) {
        const button = gameui.statusBar.constructor.prototype.addActionButton.call(this, label, callback, params);
        
        if (!button.id) {
            button.id = getRandomId("status-bar-button");
        }
        
        return button;
    }

    gameui.statusBar.addAttachedActionButton = function(attachTo, ...rest) {
        const button = this.addActionButton(...rest);
        attachTo.appendChild(button);
        return button;
    }

    dojo.connect(gameui.notifqueue, 'addToLog', () => {
        const notif = gameui._last_notif;
        
        addLogClass(notif);
        //addTooltipsToLog(gameui.getNotificationLogNode(notif), notif);
    });

    gameui.cancelLogs = function(notifIds: string[]) {
        notifIds.forEach((uid) => {
            if (this._notif_uid_to_log_id.hasOwnProperty(uid)) {
                const logId = this._notif_uid_to_log_id[uid];
                const logNode = document.getElementById(`log_${logId}`); 
                if (logNode) logNode.classList.add('cancel');
            }
            if (this._notif_uid_to_mobile_log_id.hasOwnProperty(uid)) {
                const mobileLogId = this._notif_uid_to_mobile_log_id[uid];
                const logNode = document.getElementById(`dockedlog_${mobileLogId}`);
                if (logNode) logNode.classList.add('cancel');
            }
        });
    }

    const getNotificationLogNode = function(notif: LastNotif | undefined) {
        if (!notif) {
            return null;
        }

        let logNode = document.getElementById(`log_${notif.logId}`);
        if (!logNode) {
            logNode = document.getElementById(`dockedlog_${notif.mobileLogId}`);
        }

        return logNode;
    }

    const addLogClass = function(notif: LastNotif) {
        if (!notif) {
            return;
        }

        let type = notif.msg.type;
        if (type == 'history_history') {
            type = (notif.msg.args as any).originalType;
        }

        const logNode = getNotificationLogNode(notif);

        logNode.classList.add(`notif_${type}`);

        if (onLogAdded && onLogAdded[type] && logNode) {
            onLogAdded[type](logNode, notif);
        }
    }
}