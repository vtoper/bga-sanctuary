import { setPlayerScore } from '../framework/utils';

/**
 * Handlers for the notifications that change a player's resources, markers or score.
 */
export class PlayerNotifications {
  bga: ExtendedBga;

  constructor(bga: ExtendedBga) {
    this.bga = bga;
  }

  // Gain::onEnteringState — a tile effect grants resources (currently conservation only)
  // TODO: animate the gain once the conservation track is displayed. `args.bonuses` holds the detail.
  async notif_getBonuses(args) {}

  // Conservation::actSupport — a conservation marker is placed on the association board
  // TODO: render the marker on the conservation board, it is not displayed yet.
  async notif_conservationSupported(args) {}

  // Project::actPlayProject — replacing a tile with a release project gives conservation markers
  // TODO: same, nothing to update until the conservation track is displayed.
  async notif_conservationMarkersGained(args) {}

  // Administration::finishAdministration — a player takes an end of game marker
  // TODO: render the marker in the player panel, meeples are not displayed yet.
  async notif_endTriggered(args) {}

  // Player::updateScore — final scoring
  async notif_scoring(args) {
    setPlayerScore(args.player_id, args.score ?? 0);
  }
}
