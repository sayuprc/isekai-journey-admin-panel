import { app } from '../../server/index';

const handle = ({ request }: { request: Request }) => app.handle(request);

export const ALL = handle;
