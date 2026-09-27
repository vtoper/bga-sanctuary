import { players } from '../Players';

/**
 * Handlers for the notifications sent by the engine framework (undo / restart turn).
 */
export class FrameworkNotifications {
  bga: ExtendedBga;

  constructor(bga: ExtendedBga) {
    this.bga = bga;
  }

  // Log::revertTurn — drop the log entries of the turn being restarted
  async notif_clearTurn(args) {
    this.bga.gameui.cancelLogs(args.notifIds ?? []);
  }

  // Log::revertTurn — private full state resend, used to rebuild the UI after an undo
  async notif_refreshUI(args) {
    players.refreshUI(args.data);
  }
}
