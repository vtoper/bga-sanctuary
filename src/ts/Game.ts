// import { PlayerTurn } from './States/PlayerTurn';
import { ConfirmTurn } from './framework/states/ConfirmTurn';
import { StateProcessor } from './framework/StateProcessor';
import {
  clearPersistantActionButtonsNode,
  clearRestartActionButtonsNode,
  debug,
  getRestartActionButtonsNode,
  initUtils,
} from './framework/utils';
import { ResolveChoice } from './framework/states/ResolveChoice';
import { overrideGamePrototype } from './framework/overrideGamePrototype';
import { DummyEnd } from './framework/states/DummyEnd';
import { showEngine } from './framework/engine';
import { AnytimeActions } from './framework/states/AnytimeActions';

import notifications from './notifications';
import { TakeTile } from './states/TakeTile';
import { ChooseActionCard } from './states/ChooseActionCard';

export class Game {
  bga: ExtendedBga;
  stateProcessor: StateProcessor;
  gamedatas: SanctuaryGamedatas;

  //   private playerTurn: PlayerTurn;

  constructor(bga: ExtendedBga) {
    console.log('sanctuary constructor');
    this.bga = bga;

    // Framework
    this.bga.states.register('ConfirmTurn', new ConfirmTurn(this, bga));
    this.bga.states.register('ConfirmPartialTurn', new ConfirmTurn(this, bga));
    this.bga.states.register('ResolveChoice', new ResolveChoice(this, bga));
    this.bga.states.register('client_selectAnytimeAction', new AnytimeActions(this, bga));
    this.bga.states.register('DummyEnd', new DummyEnd(this, bga));

    this.bga.states.register('TakeTile', new TakeTile(this, bga));
    this.bga.states.register('ChooseActionCard', new ChooseActionCard(this, bga));

    // Uncomment the next line to show debug informations about state changes in the console. Remove before going to production!
    this.bga.states.logger = console.log;

    this.stateProcessor = new StateProcessor(this, bga);
    initUtils(this.bga);

    // Here, you can init the global variables of your user interface
    // Example:
    // this.myGlobalValue = 0;
  }

  /*
        setup:
        
        This method must set up the game user interface according to current game situation specified
        in parameters.
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
        
        "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
    */

  setup(gamedatas: SanctuaryGamedatas) {
    console.log('Starting game setup');
    console.debug(gamedatas);
    this.gamedatas = gamedatas;

    // Setup game notifications to handle (see "setupNotifications" method below)
    this.setupNotifications();
    overrideGamePrototype(this.bga.gameui);
    console.log('Ending game setup');
  }

  ///////////////////////////////////////////////////
  //// Utility methods

  /*
    
        Here, you can defines some utility methods that you can use everywhere in your javascript
        script. Typically, functions that are used in multiple state classes or outside a state class.
    
    */

  ///////////////////////////////////////////////////
  //// Reaction to cometD notifications

  /*
        setupNotifications:
        
        In this method, you associate each of your game notifications with your local method to handle it.
        
        Note: game notification names correspond to "bga->notify->all" calls in your Game.php file.
    
    */
  setupNotifications() {
    console.log('notifications subscriptions setup');

    // automatically listen to the notifications, based on the `notif_xxx` function on this class.
    // Uncomment the logger param to see debug information in the console about notifications.
    this.bga.notifications.setupPromiseNotifications({
      logger: console.log,
      handlers: [this, ...this.bga.states.getStateClasses(), ...notifications],
      onStart: (notifName, msg, args) => {
        $('pagemaintitletext').innerHTML = msg;
        $('gameaction_status').innerHTML = msg;
      },
      onEnd: (notifName, msg, args) => {
        $('pagemaintitletext').innerHTML = '';
        $('gameaction_status').innerHTML = '';
      },
    });
  }

  onEnteringState(stateName: string, args: Gamestate) {
    console.debug('Entering state', stateName, args);
    getRestartActionButtonsNode().innerHTML = '';
    this.stateProcessor.process(args.args, args);
  }

  async notif_fillPool(args) {
    console.debug(args);
  }

  // TODO: from this point and below, you can write your game notifications handling methods

  /*
    Example:
    async notif_cardPlayed( args ) {
        // Note: args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
        
        // TODO: play the card in the user interface.
    }
    */

  async notif_showEngine(args: EngineShownArgs) {
    showEngine(args.engine);
  }
}
