import { Game } from '../Game';
import { players } from '../Players';
import { onClick } from '../framework/event';
import { clearPossible, getCurrentPlayerId, performAction } from '../framework/utils';

export class PlaceOpenAreas {
  game: Game;
  bga: ExtendedBga;

  private args: PlaceOpenAreasArgs | null = null;
  private selectedLocation: string | null = null;

  constructor(game: Game, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
  }

  onEnteringState(args: PlaceOpenAreasArgs, isCurrentPlayerActive: boolean) {
    this.args = args;
    this.selectedLocation = null;

    if (isCurrentPlayerActive) {
      this.refresh();
    }
  }

  onLeavingState(args: object, isCurrentPlayerActive: boolean) {
    this.args = null;
    this.selectedLocation = null;
    clearPossible();
  }

  private refresh() {
    clearPossible();
    this.makeLocationsSelectable();

    this.bga.statusBar.removeActionButtons();
    this.bga.statusBar.addActionButton(_('Confirm'), () => this.confirm(), {
      disabled: this.selectedLocation === null,
    });
  }

  private makeLocationsSelectable() {
    if (!this.args) {
      return;
    }

    const playerId = getCurrentPlayerId();
    for (const cell of this.args.locations) {
      const node = players.getMapCellNode(playerId, cell.x, cell.y);
      if (!node) {
        continue;
      }

      const locationKey = `${cell.x}_${cell.y}`;
      if (locationKey === this.selectedLocation) {
        node.classList.add('selected');
      }
      onClick(node, () => this.selectLocation(locationKey));
    }
  }

  private selectLocation(locationKey: string) {
    this.selectedLocation = this.selectedLocation === locationKey ? null : locationKey;
    this.refresh();
  }

  private confirm() {
    performAction('actPlaceOpenArea', { location: this.selectedLocation });
  }
}
