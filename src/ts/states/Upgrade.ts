import { Game } from '../Game';
import { clearPossible, performAction } from '../framework/utils';

export class Upgrade {
  game: Game;
  bga: ExtendedBga;

  private args: UpgradeArgs | null = null;

  constructor(game: Game, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
  }

  onEnteringState(args: UpgradeArgs, isCurrentPlayerActive: boolean) {
    this.args = args;
    if (isCurrentPlayerActive) {
      this.refresh();
    }
  }

  onLeavingState(args: object, isCurrentPlayerActive: boolean) {
    this.args = null;
    clearPossible();
  }

  private refresh() {
    clearPossible();
    this.bga.statusBar.removeActionButtons();

    for (const [tokenId, upgrade] of Object.entries(this.args?.playableUpgrades ?? {})) {
      for (const card of this.args.actionCards) {
        let cardId = card.id;
        this.bga.statusBar.addActionButton(`${_('Upgrade')} ${card.type} (${upgrade.type})`, () =>
          performAction('actUpgrade', { tokenId, cardId }),
        );
      }
    }
  }
}
