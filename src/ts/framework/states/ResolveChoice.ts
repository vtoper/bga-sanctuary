import { Game } from "../../Game";
import { checkIfIrreversible, updateDescription } from "../engine";

export class ResolveChoice {
    game: Game;
    bga: ExtendedBga;
    
    constructor(game: Game, bga: ExtendedBga) {
        this.game = game;
        this.bga = bga;
    }

    /**
     * This method is called each time we are entering the game state. You can use this method to perform some user interface changes at this moment.
     */
    onEnteringState(args: ResolveChoiceArgs, isCurrentPlayerActive: boolean) {
        if (isCurrentPlayerActive) {
            Object.values(args.choices).forEach(choice => {
                const description = updateDescription(choice.description, choice.args);

                const button = this.bga.statusBar.addActionButton(
                    description, 
                    checkIfIrreversible(choice.irreversibleAction, () => this.bga.actions.performAction('actChooseAction', { choiceId: choice.id })),
                    {color: "secondary"});        
            });

            Object.values(args.allChoices).forEach(choice => {
                if (Object.values(args.choices).find(c => c.id === choice.id)) {
                    return;
                }

                const description = updateDescription(choice.description, choice.args);
                const button = this.bga.statusBar.addActionButton(
                    description, 
                    checkIfIrreversible(choice.irreversibleAction, () => this.bga.actions.performAction('actChooseAction', { choiceId: choice.id })),
                    {color: "primary"});        

                button.disabled = true;
            });
        }
    }

    /**
     * This method is called each time we are leaving the game state. You can use this method to perform some user interface changes at this moment.
     */
    onLeavingState(args: ResolveChoiceArgs, isCurrentPlayerActive: boolean) {
    }

    /**
     * This method is called each time the current player becomes active or inactive in a MULTIPLE_ACTIVE_PLAYER state. You can use this method to perform some user interface changes at this moment.
     * on MULTIPLE_ACTIVE_PLAYER states, you may want to call this function in onEnteringState using `this.onPlayerActivationChange(args, isCurrentPlayerActive)` at the end of onEnteringState.
     * If your state is not a MULTIPLE_ACTIVE_PLAYER one, you can delete this function.
     */
    onPlayerActivationChange(args: ResolveChoiceArgs, isCurrentPlayerActive: boolean) {
    }
}