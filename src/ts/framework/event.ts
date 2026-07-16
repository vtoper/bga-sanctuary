let handles: { node: Element; event: string; group: string; handle: EventListenerOrEventListenerObject }[] = [];

export const attach = function (
    node: Element,
    handle: Function,
    event: string = 'click',
    group: string = '__default',
    options?: boolean | AddEventListenerOptions
) {
    const handleWrapper = (e: Event) => {
        handle(e, () => {
            node.removeEventListener(event, handleWrapper);
        });
    };

    node.addEventListener(event, handleWrapper, options);

    handles.push({ node, event, group, handle: handleWrapper });
};

export const onClick = function (node: Element, handle: Function) {
    attach(node, handle);
    node.classList.add('selectable');
};

export const autodetach = (handle: Function) => {
    return (e: Event, detach: Function) => {
        detach();
        handle(e);
    };
};

export const attachQuery = function (query: string, handle: Function, event: string = 'click', group?: string) {
    document.querySelectorAll(query).forEach((node) => {
        attach(node, handle, event, group);
    });
};

const detach = function (
    predicate: (handle: { node: Element; event: string; group: string; handle: EventListenerOrEventListenerObject }) => boolean
) {
    const [toRemove, toKeep] = handles.reduce(
        ([toRemove, toKeep], handle) => {
            if (predicate(handle)) {
                toRemove.push(handle);
            } else {
                toKeep.push(handle);
            }
            return [toRemove, toKeep];
        },
        [[], []]
    );

    toRemove.forEach(({ node, event, handle }) => node.removeEventListener(event, handle));
    handles = toKeep;
};

export const detachAll = function () {
    detach(() => true);
};

export const detachGroup = function (group: string) {
    detach((handle) => handle.group === group);
};

export const detachNode = function (node: Element) {
    detach((handle) => handle.node === node);
};
