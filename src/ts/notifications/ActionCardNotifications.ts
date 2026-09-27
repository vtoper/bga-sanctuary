import { players } from '../Players';

/**
 * Handlers for the action-card notifications sent by the PHP side.
 */
export class ActionCardNotifications {
  bga: ExtendedBga;

  constructor(bga: ExtendedBga) {
    this.bga = bga;
  }

  // ChooseActionCard::actChooseActionCard — the chosen card is flagged as active
  async notif_chooseActionCard(args) {
    players.setActionCardStatus(args.player_id, args.actionCard.id, 1);
  }

  // MoveActionCard::actMoveActionCard — card slid back to slot 1, the others shift up
  async notif_actionCardMoved(args) {
    players.setActionCards(args.player_id, args.actionCards);
  }

  // Cleanup — the card used this turn goes back to slot 1 and stops being active
  async notif_actionCardCleanup(args) {
    players.setActionCards(args.player_id, args.actionCards);
    players.setActionCardStatus(args.player_id, args.actionCard.id, 0);
  }

  // Upgrade::actUpgrade — an upgrade token turns a level I card into a level II card
  async notif_actionCardUpgraded(args) {
    players.setActionCardLevel(args.player_id, args.card_id, args.card?.level ?? 2);
  }
}
