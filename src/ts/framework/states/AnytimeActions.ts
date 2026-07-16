import { Game } from '../../Game';
import { updateDescription } from '../engine';

export class AnytimeActions {
    game: Game;
    bga: ExtendedBga;

    constructor(game: Game, bga: ExtendedBga) {
        this.game = game;
        this.bga = bga;
    }

    /**
     * This method is called each time we are entering the game state. You can use this method to perform some user interface changes at this moment.
     */
    onEnteringState(args: { anytimeActions: AnytimeAction[] }, isCurrentPlayerActive: boolean) {
        for (const action of args.anytimeActions) {
            this.bga.statusBar.addActionButton(
                updateDescription(action.description, {}),
                () => this.bga.actions.performAction('actAnytimeAction', { actionId: action.id }),
                { color: 'secondary' }
            );
        }
        this.bga.statusBar.addActionButton(_('Cancel'), () => this.bga.states.restoreServerGameState(), { color: 'alert' });
    }

    /**
     * This method is called each time we are leaving the game state. You can use this method to perform some user interface changes at this moment.
     */
    onLeavingState(args: { anytimeActions: AnytimeAction[] }, isCurrentPlayerActive: boolean) {}

    /**
     * This method is called each time the current player becomes active or inactive in a MULTIPLE_ACTIVE_PLAYER state. You can use this method to perform some user interface changes at this moment.
     * on MULTIPLE_ACTIVE_PLAYER states, you may want to call this function in onEnteringState using `this.onPlayerActivationChange(args, isCurrentPlayerActive)` at the end of onEnteringState.
     * If your state is not a MULTIPLE_ACTIVE_PLAYER one, you can delete this function.
     */
    onPlayerActivationChange(args: { anytimeActions: AnytimeAction[] }, isCurrentPlayerActive: boolean) {}
}
