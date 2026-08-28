import { Game } from '../Game';
import { clearPossible, performAction } from '../framework/utils';

export class Conservation {
  game: Game;
  bga: ExtendedBga;

  private args: ConservationArgs | null = null;

  constructor(game: Game, bga: ExtendedBga) {
    this.game = game;
    this.bga = bga;
  }

  /**
   * This method is called each time we are entering the game state. You can use this method to perform some user interface changes at this moment.
   */
  onEnteringState(args: ConservationArgs, isCurrentPlayerActive: boolean) {
    this.args = args;
    this.resetSelection();

    if (!isCurrentPlayerActive) {
      return;
    }

    this.refresh();
  }

  /**
   * This method is called each time we are leaving the game state. You can use this method to perform some user interface changes at this moment.
   */
  onLeavingState(args: object, isCurrentPlayerActive: boolean) {
    this.args = null;
    this.resetSelection();
    clearPossible();
  }

  private resetSelection() {}

  private refresh() {
    clearPossible();
    this.updateStatusBar();
  }

  private updateStatusBar() {
    this.bga.statusBar.removeActionButtons();

    if (!this.args) {
      return;
    }

    for (const [markerId, marker] of Object.entries(this.args.playableMarkers)) {
      for (const supported of marker.achievements) {
        const requiredMarkers = marker.conservationMarkers?.[supported] ?? 0;
        const strengthSuffix = ` (${marker.strength})`;
        const markerSuffix = requiredMarkers > 0 ? ` (+${requiredMarkers} marker)` : '';
        this.bga.statusBar.addActionButton(`${_('Support')} ${supported}${strengthSuffix}${markerSuffix}`, () =>
          this.confirm(markerId, supported),
        );
      }
    }
  }

  private confirm(markerId: string, supported: string) {
    performAction('actConservation', { markerId, supported });
  }
}
